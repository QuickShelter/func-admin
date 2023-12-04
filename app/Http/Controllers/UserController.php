<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Models\User;
use DB;
use Carbon\Carbon;
use Mail;
use App\Mail\MakeAdmin;

class UserController extends BaseController
{
    public function filter(Request $request)
    {
        try { 
        $users = new User;
        if ($request->role)
            $users = $users->where('roles', 'LIKE' , "%{$request->role}%");
        
        // Search Query
        if ($request->has('q') && $request->q != null){
            $users = $users->where(function($query) use ($request){
                $query->where('phone_number', 'LIKE', "%{$request->q}%")
                    ->orWhere('first_name', 'LIKE', "%{$request->q}%")
                    ->orWhere('last_name', 'LIKE', "%{$request->q}%")
                    ->orWhere('email', 'LIKE', "%{$request->q}%");
                return $query;
            });
        }

        if ($request->from_date != null && $request->to_date) {
            if($request->from_date < $request->to_date) {
                $users = $users->whereDate('created_at', '>=', new Carbon($request->from_date));
                $users = $users->whereDate('created_at', '<=', new Carbon($request->to_date));
            }
        }

        

        $users = $users->orderBy("id", $request->sort == "asc" ? "asc": "desc");
            if($request->has('export')) {
                $users = $users->get();
                if($request->role)
                    $this->exportData($users, $request->export, $request->role);
                else 
                    $this->exportData($users, $request->export);
            } else {
                $users = $users->paginate( 15, ['*'], 'page');
                return $this->sendResponse($users, 'Returned users');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error.', ['error' => $e->getMessage()], 400);
        }
    }

    public function metric(Request $request)
    {
        try {
            $statistics = DB::select("SELECT count(id) as total,
                        (SELECT count(b.id) FROM users b WHERE b.roles LIKE '%buyer%' AND deleted_at IS NULL) as total_buyers,
                        (SELECT count(s.id) FROM users s WHERE s.roles LIKE '%seller%' AND deleted_at IS NULL) as total_sellers,
                        (SELECT count(a.id) FROM users a WHERE a.roles LIKE '%admin%' AND deleted_at IS NULL) as total_admins 
                        FROM users WHERE deleted_at IS NULL");
            return $this->sendResponse($statistics, 'Returned users analytics');
        } catch (\Exception $e) {
            return $this->sendError('Error.', ['error' => $e->getMessage()], 400);
        }
    }

    private function exportData($users, $type, $role = 'all')
    {
        ob_start();
        $fileName = $role."-report-" . time() . ".csv"; 
        // Headers for download 
        $columns = [ 
            'First Name', 
            'Last Name', 
            'Email' , 
            'Phone Number', 
            'Roles', 
            'Pension Fund Admin', 
            'Created At', 
            'Preferred Property Location'
        ];
        $data = [];
        foreach($users as $c => $user) {
            $data[$c][] = $user->first_name;
            $data[$c][] = $user->last_name;
            $data[$c][] = $user->email;
            $data[$c][] = $user->phone_number;
            //$data[$c][] = implode(",", $user->roles);
            $data[$c][] = $user->pension_fund_admin;
            $data[$c][] = $user->created_at;
            $data[$c][] = $user->preferred_property_location;
        }
        $output = fopen( $fileName, 'w' );
        //ob_end_clean();
        // Write headers to CSV file.
        fputcsv( $output, $columns );

        // Loop through the prepared data to output it to CSV file.
        foreach( $data as $dataItem ){
            fputcsv( $output, $dataItem );
        }
        
        // Close the file pointer with PHP with the updated output.
        fclose( $output );
        if($type == 'csv')
            header('Content-Type: text/csv; charset=utf-8');

        if ($type == 'pdf') {
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            //header("Content-Disposition: attachment; filename=$fileName");
            header("Content-Type: application/pdf");
            header("Content-Transfer-Encoding: binary");
        }

        header("Content-Disposition: attachment; filename=\"$fileName\"");
        readfile($fileName);
        throw new \Exception('Invalid download type');
    }

    public function updateUserProfile($userId, Request $request)
    {
        try {
        $user = User::find($userId);
        $this->validate($request, [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone_number' => 'required|numeric|unique:users,email,'.$user->id
        ]);
        if(!$user) throw new \Exception('User does not exist');

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number
        ]);

        return $this->sendResponse($user, 'User profile updated');
        } catch (\Exception $e) {
            return $this->sendError('Error.', ['error' => $e->getMessage()], 400);
        }
    }

    public function makeAdmin($userId, Request $request)
    {
        try {
            $user = User::find($userId);
            
            if(!$user) throw new \Exception('User does not exist');
            //if(in_array('admin', $user->roles)) throw new \Exception('Already an admin');

            $newRole = (array)'admin';
            $roles = array_merge($newRole, $user->roles);
            $user->update(['roles' => $roles]);
            $mailData = ['role' => 'admin', 'user' => $user];
            Mail::to($user->email)->send(new MakeAdmin($mailData));
            return $this->sendResponse($user, 'User profile updated');
        } catch (\Exception $e) {
            return $this->sendError('Error.', ['error' => $e->getMessage()], 400);
        }
    }

    public function getUser($userId, Request $request)
    {
        try {
            $user = User::with('properties.photos')->find($userId);
            return $this->sendResponse($user, 'User profile updated');
        } catch (\Exception $e) {
            return $this->sendError('Error.', ['error' => $e->getMessage()], 400);
        }
    }
}
