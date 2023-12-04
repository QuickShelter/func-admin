<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Models\Transaction;

class TransactionController extends BaseController
{
    public function transactions(Request $request)
    {
        try {
            $transactions = Transaction::paginate( 15, ['*'], 'page');
            return $this->sendResponse($transactions, 'Success message');
        } catch (\Exception $e) {
            return $this->sendError('Error.', ['error' => $e->getMessage()], 400);
        }
    }

    public function transaction($transactionId, Request $request)
    {
        try {
            $errorCode = '';
            $transaction = Transaction::with(['user', 'property'])
                            ->where('id',$transactionId)
                            ->first();
            if(!$transaction) {
                $errorCode = 404;
                throw new \Exception('Not found');
            }
            return $this->sendResponse($transaction, 'Success message');
        } catch (\Exception $e) {
            $errorCode = $errorCode == '' ? 400 : $errorCode;
            return $this->sendError('Error.', ['error' => $e->getMessage()], $errorCode);
        }
    }
}
