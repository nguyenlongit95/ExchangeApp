<?php

namespace App\Helpers;

use App\Components\Api\ApiConst as ApiConst;
use App\Http\Controllers\Api\Repository\AppVersionRepository;

class ResponseAPI {

    /**
     * Response Output Api.
     *
     * @param object $data
     *
     * @return string
     */
    public function responseApi($data)
    {
        if (is_array((array)$data) && count($data) > 0) {
            return $this->responseWithData($this->objectToArray($data));
        } else {
            return $this->responseNoContent();
        }
    }

    /**
     * Convert Object to Array.
     *
     * @param Object $object
     *
     * @return array
     */
    protected function objectToArray($object)
    {
        if (is_object($object)) {
            return json_decode(json_encode($object), true);
        }
        // $object is array
        if (is_array($object)) {
            return $object;
        }

        return [];
    }

    /**
     * Return a 200 - OK message or a custom message.
     *
     * @param $aryOutput
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseOk($aryOutput)
    {
        $appVersionRepository = new AppVersionRepository();
        $aryOutput['force_update'] = $appVersionRepository->getUpdateVersion();
        return response()->json($aryOutput, ApiConst::API_STATUS_OK, $this->headers());
    }


    /**
     * Return a 404 - Not Found message or a custom message.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseNotFound()
    {
        $aryOutput = $this->setOutput(ApiConst::API_STATUS_NOT_FOUND, trans('message.not_found'));
        $appVersionRepository = new AppVersionRepository();
        $aryOutput['force_update'] = $appVersionRepository->getUpdateVersion();
        return $this->responseOk($aryOutput);
    }

    /**
     * Return a 7 - Transaction is failure.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseTransactionError()
    {
        $aryOutput = $this->setOutput(ApiConst::API_ERROR_TRANSACTION, trans('message.transaction_error'));
        $appVersionRepository = new AppVersionRepository();
        $aryOutput['force_update'] = $appVersionRepository->getUpdateVersion();
        return $this->responseOk($aryOutput);
    }

    public function responseNotFoundRecord($fieldNameArr)
    {
        $errorArr = [];
        foreach ((array)$fieldNameArr as $field) {
            $errorArr[] = [
                'error_code' => $field,
                'message' => $field . 'が' . trans('message.not_found')
            ];
        }
        $aryOutput =
            [
                'status' => ApiConst::API_STATUS_BAD_REQUEST,
                'errors' => $errorArr
            ];
        $appVersionRepository = new AppVersionRepository();
        $aryOutput['force_update'] = $appVersionRepository->getUpdateVersion();

        return response()->json($aryOutput, ApiConst::API_STATUS_BAD_REQUEST, $this->headers());
    }

    /**
     * Return a 204 - No Content message or a custom message.
     **
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseNoContent()
    {
        $appVersionRepository = new AppVersionRepository();
        $appVersion = $appVersionRepository->getUpdateVersion();

        $statusHeader = ApiConst::API_STATUS_NO_CONTENT;
        if ($appVersion['status'] == 1) {
            $statusHeader =  ApiConst::API_STATUS_OK;
        }

        $aryOutput =
            [
                'status' => $statusHeader,
                'errors' => [[
                    'error_code' => 'no_content',
                    'message' => trans('message.no_content')]
                ]];

        $aryOutput['force_update'] = $appVersionRepository->getUpdateVersion();

        return response()->json($aryOutput, $statusHeader, $this->headers());
    }

    /**
     * Return a 500 - Internal Error  message or a custom message.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function responseInternalError()
    {
        return $this->responseWithError(
            ApiConst::API_CODE_INTERNAL_ERROR,
            trans('message.internal_server_error'),
            ApiConst::API_STATUS_INTERNAL_ERROR
        );
    }

    public function responseNotAccept($field)
    {
        return $this->responseWithError($field, ApiConst::MSG_NOT_NOT_ACCEPTABLE, ApiConst::API_STATUS_NOT_ACCEPTABLE);
    }

    public function responseForbidden(){
        $aryOutput =
            [
                'status' => ApiConst::API_STATUS_FORBIDDEN,
                'errors' => [[
                    'error_code' => 'forbidden',
                    'message' => trans('message.msg_403')]
                ]];
        return response()->json($aryOutput, ApiConst::API_STATUS_FORBIDDEN, $this->headers());
    }


    /**
     * Return a  response with data.
     *
     * @param array $data
     *
     * @return Response output for api
     */
    public function responseWithData($data)
    {
        // set array out put
        $aryOutput = $this->setOutput();
        // set data to out put
        $aryOutput['data'] = array_merge($aryOutput['data'], $data);
        unset($aryOutput['errors']);
        // response of api output
        return $this->responseOk($aryOutput);
    }

    /**
     * Give special extra headers to the response.
     *
     *
     * @return array headers
     */
    public function headers()
    {
        // set content type default json
        $contentType = 'application/json;charset=UTF-8;';
        // return header array
        return [
            'Content-Type' => $contentType,
            'X-Powered-By' => trans('message.api_author'),
            'X-Api-Version' => ApiConst::API_VERSION,
        ];
    }

    /**
     * response with a success or errors message.
     *
     * @param array|string $arrOutput
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function response($arrOutput)
    {
        return response()->json($arrOutput, ApiConst::API_STATUS_OK, $this->headers());
    }

    /**
     * @param string $errors
     * @param int $statusCode
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function responseValidateFail($errors, $status = ApiConst::API_STATUS_BAD_REQUEST)
    {

        $aryOutput = $this->setOutput($status, $errors);
        unset($aryOutput['data']);
        $appVersionRepository = new AppVersionRepository();
        $aryOutput['force_update'] = $appVersionRepository->getUpdateVersion();
        return response()->json($aryOutput, $status, $this->headers());
    }

    /**
     * @param $errorText
     * @param int $errorCode
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseValidateAuthor($errorText, $errorCode = ApiConst::API_CODE_FIELD_INVALID)
    {
        return $this->responseWithError($errorCode, $errorText, ApiConst::API_STATUS_UNAUTHORIZED);
//        $arrResponse = [
//            'status' => ApiConst::API_STATUS_UNAUTHORIZED,
//            'errors' => [
//                $this->setErrorsData($errorCode, $errorText)
//            ]
//        ];
//        return response()->json($arrResponse, ApiConst::API_STATUS_UNAUTHORIZED, $this->headers());
    }

    /**
     * @param array $errorCode
     * @param string $errorText
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function validateAuthor($errorCode, $errorText)
    {
        return $this->responseWithError($errorCode, $errorText, ApiConst::API_STATUS_BAD_REQUEST);
    }

    /**
     * @param int $errorCode
     * @param string $errorText
     * @param int $status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseWithError($errorCode = ApiConst::API_FIELD_INVALID, $errorText = '', $status = ApiConst::API_STATUS_BAD_REQUEST)
    {
        $arrResponse = [
            'status' => $status,
            'errors' => [
                $this->setErrorsData($errorCode, $errorText)
            ]
        ];
        $appVersionRepository = new AppVersionRepository();
        $arrResponse['force_update'] = $appVersionRepository->getUpdateVersion();
        return response()->json($arrResponse, $status, $this->headers());
    }

    /**
     * Set Data Errors.
     *
     * @param string $errorCode
     * @param string $errorText
     *
     * @return array Errors
     */
    public function setErrorsData($errorCode, $errorText)
    {
        return [
            'error_code' => $errorCode,
            'message' => $errorText,
        ];
    }

    /**
     * @param int $statusCode
     * @param array $errors
     *
     * @return array
     */
    public function setOutput($statusCode = ApiConst::API_STATUS_OK, $errors = [])
    {
        return [
            'status' => $statusCode,
            'data' => [
            ],
            'errors' => $this->getErrorsValidate($errors)
        ];
    }

    /**
     * Get Message Validate.
     *
     * @param array|string $errorText
     *
     * @return string message of response api
     */
    public function getMsgValidate($errorText)
    {
        return (is_array($errorText) or is_object($errorText)) ? $errorText->first() : $errorText;
    }

    /**
     * get errors validate
     * @param $errors
     * @return array
     */
    public function getErrorsValidate($errors)
    {
        $arrErrors = [];
        if (!empty($errors) and (is_array($errors) or is_object($errors))) {
            $errors = $errors->toArray();
            foreach ($errors as $key => $error) {
                $arrErrors[] = [
                    'error_code' => $key,
                    'message' => $error[0]
                ];
            }
        }

        return $arrErrors;
    }

}
