<?php

namespace App\Traits;

use Illuminate\Http\Response;

trait ApiResponse
{
   public function sendResponse($message, $data, $code=Response::HTTP_OK){
       return  response()->json(
           [
               "success"=>true,
               "message"=>$message,
               "data"=>$data
           ] , $code
       );
   }
   public function sendError($message, $errors = [], $code= Response::HTTP_INTERNAL_SERVER_ERROR){
       $response = [
           'success' => false,
           'message' => $message,
       ];
       if(!empty($errors)){
           $response['data'] = $errors;
       }
       return response()->json($response, $code);
    }
}
