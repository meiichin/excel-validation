<?php

namespace Avana\ExcelValidation;

use Excel;
use Exception;
use Avana\ExcelValidation\ImportExcel;

/**
 * All Class to validation
 */
class ExcelValidation
{

    /**
     * format Response
     *
     * @var array
     */
    protected static $response = [
        'status' => true,
        'message' => null,
        'result' => null
    ];

    /**
     * validate Data Excel
     *
     * @param [type] $data
     * @return void
     */
    public static function validate($data)
    {
        try {
            // checking file requirement
            $check_file = pathinfo($data);

            // validate extension file
            if (!isset($check_file['extension']) || ($check_file['extension'] != 'xls' && $check_file['extension'] != 'xlsx')) {
                throw new \Exception("Format yang kamu masukan bukan xls atau xlsx");
            }

            // parse to array every sheet and cell & row from the excel
            $excel = Excel::toArray(new ImportExcel, $data);

            $check_error =  self::errorCheck($excel[0]);

            if (isset($check_error['status']) && $check_error['status']) {
                return $check_error;
            }

            self::$response['message']  = $excel;

            return response()->json(self::$response, 200);
        } catch (Exception $error) {

            // set response excel status
            self::$response['status']   = false;
            self::$response['message']  = $error->getMessage() ?? 'Error can\'t process file';

            return response()->json(self::$response, $error->getCode() != 0 ? $error->getCode() : 400);
        }

    }

    /**
     * Error Checking with param array from excel
     * header must should containt only 5
     *
     * @param Array $data
     * @return Array
     */
    static function errorCheck($data)
    {
        $error_message = null;

        foreach ($data as $row_key => $row_value) {
            foreach ($row_value as $key => $value) {
                if ($row_key == 0) {
                    if (substr($value, 0, 1) == '#')
                        $rule[$key] = 'no_space';

                    if (substr($value, -1) == '*')
                        $rule[$key] = 'required';

                    $field[$key] = $value;
                } else {

                    $check_rule = self::ruleCheck($field[$key], $value, $rule[$key] ?? null);

                    //check rule
                    if (isset($check_rule['status']) && $check_rule['status']) {
                        $error_message[] = [
                            'row'       => $row_key,
                            'error'      => $check_rule['message'],
                        ];
                    }
                }
            }
        }

        return [
            'status' => true,
            'message' => $error_message,
        ];
    }

    /**
     * Check rule
     *
     * @param String $column
     * @param String $value
     * @param Enum $rule [no_space, require, null]
     * @return Json
     */
    static function ruleCheck($column, $value, $rule)
    {
        if ($rule == "no_space" && strpos($value, ' ')) {
            return [
                'status'    => true,
                'message'   => $column.' should not contain any space'
            ];
        }
        if ($rule == "required" && $value == null) {
            return [
                'status'    => true,
                'message'   => 'Missing value in '.$column
            ];
        }

        return [
            'status' => false
        ];
    }

}
