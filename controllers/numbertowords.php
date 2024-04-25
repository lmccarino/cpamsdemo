<?php

class numbertowordconverter {
    function convert_number($number) 
    {
        if (($number < 0) || ($number > 999999999)) 
        {
            throw new Exception("Number is out of range");
        }
        $giga = floor($number / 1000000);
        // Millions (giga)
        $number -= $giga * 1000000;
        $kilo = floor($number / 1000);
        // Thousands (kilo)
        $number -= $kilo * 1000;
        $hecto = floor($number / 100);
        // Hundreds (hecto)
        $number -= $hecto * 100;
        $deca = floor($number / 10);
        // Tens (deca)
        $n = $number % 10;
        // Ones
        $result = "";
        if ($giga) 
        {
            $result .= $this->convert_number($giga) .  "MILLION";
        }
        if ($kilo) 
        {
            $result .= (empty($result) ? "" : " ") .$this->convert_number($kilo) . " THOUSAND";
        }
        if ($hecto) 
        {
            $result .= (empty($result) ? "" : " ") .$this->convert_number($hecto) . " HUNDRED";
        }
        $ones = array("", "ONE", "TWO", "THREE", "FOUR", "FIVE", "SIX", "SEVEN", "EIGHT", "NINE", "TEN", "ELEVEN", "TWELVE", "THIRTEEN", "FOURTEEN", "FIFTEEN", "SIXTEEN", "SEVENTEEN", "EIGHTEEN", "NINETEEN");
        $tens = array("", "", "TWENTY", "THIRTY", "FORTY", "FIFTY", "SIXTY", "SEVENTY", "EIGHTY", "NINETY");
        if ($deca || $n) {
            if (!empty($result)) 
            {
                $result .= " AND ";
            }
            if ($deca < 2) 
            {
                $result .= $ones[$deca * 10 + $n];
            } else {
                $result .= $tens[$deca];
                if ($n) 
                {
                    $result .= "-" . $ones[$n];
                }
            }
        }
        if (empty($result)) 
        {
            $result = "ZERO";
        }
        return $result;
    }

    //with decimal
    // function numberTowords(float $amount)
    // {
    //    $amount_after_decimal = round($amount - ($num = floor($amount)), 2) * 100;
	//    $Onescentavos = fmod($amount_after_decimal, 10);
    //    $Tenthcentavos = $amount_after_decimal - $Onescentavos;
       
    //    // Check if there is any number after decimal
    //    $amt_hundred = null;
    //    $count_length = strlen($num);
    //    $x = 0;
    //    $string = array();
    //    $change_words = array(0 => '', 1 => 'One', 2 => 'Two',
    //      3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
    //      7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
    //      10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
    //      13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
    //      16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
    //      19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
    //      40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
    //      70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');
    //   $here_digits = array('', 'Hundred','Thousand','Hundred', 'Crore');

	//    if (!empty($change_words[$amount_after_decimal]))
	// 	   $centy = $change_words[$amount_after_decimal];
	//    else
	// 	   $centy = ($change_words[$Tenthcentavos] . "  " . $change_words[intval(round($Onescentavos))]);
    //   while( $x < $count_length ) {
    //        $get_divider = ($x == 2) ? 10 : 100;
    //        $amount = floor($num % $get_divider);
    //        $num = floor($num / $get_divider);
    //        $x += $get_divider == 10 ? 1 : 2;
    //        if ($amount) {
    //          $add_plural = (($counter = count($string)) && $amount > 9) ? 's' : null;
    //          $amt_hundred = ($counter == 1 && $string[0]) ? ' ' : null;
    //          $string [] = ($amount < 21) ? $change_words[$amount].' '. $here_digits[$counter]. $add_plural.' 
    //          '.$amt_hundred:$change_words[floor($amount / 10) * 10].' '.$change_words[$amount % 10]. ' 
    //          '.$here_digits[$counter].$add_plural.' '.$amt_hundred;
    //          }else $string[] = null;
    //        }
    //    $implode_to_Rupees = implode('', array_reverse($string)) . 'PESOS ';
    //    $get_paise = ($amount_after_decimal > 0) ? "And " . $centy . ' CENTS' : '';
    //    return strtoupper(($implode_to_Rupees ? $implode_to_Rupees : '') . $get_paise);
      
    // }

    function numberTowords(float $amount) {
        $amount_after_decimal = round($amount - ($num = floor($amount)), 2) * 100;
        $Onescentavos = fmod($amount_after_decimal, 10);
        $Tenthcentavos = $amount_after_decimal - $Onescentavos;

        // Check if there is any number after the decimal
        $amt_hundred = null;
        $count_length = strlen($num);
        $x = 0;
        $string = array();
           $change_words = array(0 => '', 1 => 'One', 2 => 'Two',
         3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
         7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
         10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
         13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
         16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
         19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
         40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
         70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');

        $here_digits = array('', 'Hundred', 'Thousand', 'Hundred', 'Crore');

        if (!empty($change_words[$amount_after_decimal]))
            $centy = $change_words[$amount_after_decimal];
        else
            $centy = ($change_words[$Tenthcentavos] . "  " . $change_words[intval(round($Onescentavos))]);

        while ($x < $count_length) {
            $get_divider = ($x == 2) ? 10 : 100;
            $amount = floor($num % $get_divider);
            $num = floor($num / $get_divider);
            $x += $get_divider == 10 ? 1 : 2;
            if ($amount) {
                $add_plural = (($counter = count($string)) && $amount > 9) ? 's' : null;
                // Modify this line to prevent the extra space before "Hundred"
                $amt_hundred = ($counter == 1 && $string[0]) ? '' : null;
                $string[] = ($amount < 21) ? $change_words[$amount] . ' ' . $here_digits[$counter] . $add_plural . ' ' . $amt_hundred : $change_words[floor($amount / 10) * 10] . ' ' . $change_words[$amount % 10] . ' ' . $here_digits[$counter] . $add_plural . ' ' . $amt_hundred;
            } else $string[] = null;
        }

        $implode_to_Rupees = implode('', array_reverse($string)) . 'PESOS ';
        $get_paise = ($amount_after_decimal > 0) ? "And " . $centy . ' CENTS' : '';
        return strtoupper(($implode_to_Rupees ? $implode_to_Rupees : '') . $get_paise);

    }
}
