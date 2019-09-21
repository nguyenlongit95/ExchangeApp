<?php
namespace App\Helpers;

class checkSymBol{

    public function getCurrencyCode(){
        $CurrencyCode = CurrencyCode::select("code","vname")->get();
        $arrayCurrencyCode = array();
        foreach($CurrencyCode as $key=>$value){
            if($value->code == "EUR"){
                array_push($arrayCurrencyCode, $value->code);
            }else if($value->code == "GBP"){
                array_push($arrayCurrencyCode, $value->code);
            }else if($value->code == "JPY"){
                array_push($arrayCurrencyCode, $value->code);
            }else if($value->code == "KRW"){
                array_push($arrayCurrencyCode, $value->code);
            }else if($value->code == "HKD"){
                array_push($arrayCurrencyCode, $value->code);
            }else if($value->code == "CHF"){
                array_push($arrayCurrencyCode, $value->code);
            }else if($value->code == "THB"){
                array_push($arrayCurrencyCode, $value->code);
            }else if($value->code == "AUD"){
                array_push($arrayCurrencyCode, $value->code);
            }else if($value->code == "CAD"){
                array_push($arrayCurrencyCode, $value->code);
            }else if($value->code == "SGD"){
                array_push($arrayCurrencyCode, $value->code);
            }else if($value->code == "SEK"){
                array_push($arrayCurrencyCode, $value->code);
            }else if($value->code == "LAK"){
                array_push($arrayCurrencyCode, $value->code);
            }else if($value->code == "DKK"){
                array_push($arrayCurrencyCode, $value->code);
            }else if($value->code == "NOK"){
                array_push($arrayCurrencyCode, $value->code);
            }else if($value->code == "CNY"){
                array_push($arrayCurrencyCode, $value->code);
            }else if($value->code == "RUB"){
                array_push($arrayCurrencyCode, $value->code);
            }else if($value->code == "NZD"){
                array_push($arrayCurrencyCode, $value->code);
            }else if($value->code == "MYR"){
                array_push($arrayCurrencyCode, $value->code);
            }else if($value->code == "TWD"){
                array_push($arrayCurrencyCode, $value->code);
            }
        }
        return $arrayCurrencyCode;
    }

    public function checkSymbol($code){
        if($code == "EUR"){
            return "&#8364;";
        }else if($code == "GBP"){
            return "&#163;";
        }else if($code == "JPY"){
            return "&#165;";
        }else if($code == "KRW"){
            return "&#8361;";
        }else if($code == "HKD"){
            return "&#65504;";
        }else if($code == "CHF"){
            return "&#65504;";
        }else if($code == "THB"){
            return "&#3647;";
        }else if($code == "AUD"){
            return "&#8371;";
        }else if($code == "CAD"){
            return "&#36;";
        }else if($code == "SGD"){
            return "&#36;";
        }else if($code == "SEK"){
            return "&#8364;";
        }else if($code == "LAK"){
            return "&#8365;";
        }else if($code == "DKK"){
            return "&#36;";
        }else if($code == "NOK"){
            return "&#36;";
        }else if($code == "CNY"){
            return "&#165;";
        }else if($code == "RUB"){
            return "&#8381;";
        }else if($code == "NZD"){
            return "&#36;";
        }else if($code == "MYR"){
            return "&#8357;";
        }else if($code == "TWD"){
            return "&#36;";
        }
    }

}

?>
