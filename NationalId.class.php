<?php
/**
 * PHP Class for formatting, generating, and verifying national identification numbers.
 *
 * Currently supported numbers:
 * - Canadian Social Insurance Number (SIN)
 * - Canadian Ontario Health Number (OHIP)
 * - United States Social Security Number (SSN)
 * 
 * The following information comes from http://www.hackcanada.com/canadian/other/sin.html
 *
 * The first digit of a Canadian SIN indicates the province of registration.
 * 
 * 1 = NB, NF, NS, PE
 * 2 = QC
 * 3 = Not Used? QC?
 * 4 = ON
 * 5 = ON
 * 6 = AB, MB, SK, NT, NU?
 * 7 = BC, YU
 * 8 = Not Used
 * 9 = Immigrants & other temp SIN's
 * 0 = Not Used
 *
 * The SIN algorithm is commonly known as the LUHN algorithm or the mod-10 algorithm. 
 * It also happens to be used to validate Credit Card numbers among other things.
 * 
 * The Canadian government uses the same authentication algorithm on many, if not all, of its "unique" numbers. 
 * It is used for employer account numbers, trust numbers, Income Tax Filer identification (your H&R Block e-file rep), 
 * and the first nine digits of the Business Number (BN). 
 * 
 * When there are letters in the identification number the following table is used to convert the letters to numbers:
 * 
 * A    B    C    D    E    F    G    H    I
 * J    K    L    M    N    O    P    Q    R
 *      S    T    U    V    W    X    Y    Z
 * -----------------------------------------
 * 1    2    3    4    5    6    7    8    9
 *
 *
 * The following information comes from http://www.usrecordsearch.com/ssn.htm
 *
 *  001-003 NH    400-407 KY    530     NV 
 *  004-007 ME    408-415 TN    531-539 WA
 *  008-009 VT    416-424 AL    540-544 OR
 *  010-034 MA    425-428 MS    545-573 CA
 *  035-039 RI    429-432 AR    574     AK
 *  040-049 CT    433-439 LA    575-576 HI
 *  050-134 NY    440-448 OK    577-579 DC
 *  135-158 NJ    449-467 TX    580     VI Virgin Islands
 *  159-211 PA    468-477 MN    581-584 PR Puerto Rico
 *  212-220 MD    478-485 IA    585     NM
 *  221-222 DE    486-500 MO    586     PI Pacific Islands*
 *  223-231 VA    501-502 ND    587-588 MS
 *  232-236 WV    503-504 SD    589-595 FL
 *  237-246 NC    505-508 NE    596-599 PR Puerto Rico
 *  247-251 SC    509-515 KS    600-601 AZ
 *  252-260 GA    516-517 MT    602-626 CA
 *  261-267 FL    518-519 ID    627-645 TX
 *  268-302 OH    520     WY    646-647 UT
 *  303-317 IN    521-524 CO    648-649 NM
 *  318-361 IL    525     NM    *Guam, American Samoa, 
 *  362-386 MI    526-527 AZ     Philippine Islands, 
 *  387-399 WI    528-529 UT     Northern Mariana Islands
 *
 *  650-699 unassigned, for future use
 *  700-728 Railroad workers through 1963, then discontinued
 *  729-799 unassigned, for future use
 *  800-999 not valid SSNs.  Some sources have claimed that numbers
 *          above 900 were used when some state programs were converted
 *          to federal control, but current SSA documents claim no
 *          numbers above 799 have ever been used.
 */ 
class UniqueId
{
        const US_SSN_VALID_LENGTH                        =   9;
        const US_SSN_INVALID_LENGTH                        = 100;
        const US_SSN_INVALID_AREA_NUMBER                = 101;
        const US_SSN_INVALID_GROUP_CODE                        = 102;
        const US_SSN_INVALID_SERIAL_NUMBER                = 103;
        
        const CA_SIN_VALID_LENGTH                        =   9;
        const CA_SIN_INVALID_LENGTH                        = 100;
        const CA_SIN_INVALID_CHECKDIGIT                        = 101;
        
        
        /**
         * Format a Unique ID number.
         *
         * @author André Fortin <andre.v.fortin@gmail.com>
         */
         public function format( $country_code = 'CA', $type = 'SIN', $id_number = '000-000-000' )
        {
                $result = array();
                switch ( strtoupper( $country_code ) )
                {
                        case 'CA':
                                switch( strtoupper( $type ) )
                                {
                                        case 'SIN':
                                                $result = $this->format_CA_SIN( $id_number );
                                                break;
                                        case 'OHIP':
                                                $result = $this->format_CA_OHIP( $id_number );
                                                break;
                                        default:
                                                break;
                                }
                                break;
                                
                        case 'US':
                                switch( $type )
                                {
                                        case 'SSN':
                                                $result = $this->format_US_SSN( $id_number );
                                                break;
                                        default:
                                                break;
                                }
                                break;
                        default:
                                break;
                }
                
                return $result;
        }
        
        
        /**
         * Generate a federal id number.
         *
         * @author André Fortin <andre.v.fortin@gmail.com>
         */
        public function generate( $country_code = 'CA', $type = 'SIN', $region = '' )
        {
                $result = array();
                switch ( strtoupper( $country_code ) )
                {
                        case 'CA':
                                switch( strtoupper( $type ) )
                                {
                                        case 'SIN':
                                                $result = $this->generate_CA_SIN( strtoupper( $region ) );
                                                break;
                                        case 'OHIP':
                                                $result = $this->generate_CA_OHIP();
                                                break;
                                        default:
                                                break;
                                }
                                break;
                                
                        case 'US':
                                switch( $type )
                                {
                                        case 'SSN':
                                                $result = $this->generate_US_SSN( strtoupper( $region ) );
                                                break;
                                        default:
                                                break;
                                }
                                break;
                        default:
                                break;
                }
                
                return $result;
        }
        
        
        /**
         * Validate a national id number.
         *
         * @author André Fortin <andre.v.fortin@gmail.com>
         */
        public function validate( $country_code = 'CA', $type = 'SIN', $id_number = '000-000-000' )
        {
                $result = array();
                switch ( strtoupper( $country_code ) )
                {
                        case 'CA':
                                switch( strtoupper( $type ) )
                                {
                                        case 'SIN':
                                                $result = $this->validate_CA_SIN( $id_number );
                                                break;
                                         default:
                                                break;
                                }
                                break;
                                
                        case 'US':
                                switch( $type )
                                {
                                        case 'SSN':
                                                $result = $this->validate_US_SSN( $id_number );
                                                break;
                                        default:
                                                break;
                                }
                                break;
                        default:
                                break;
                }
                
                return $result;
        }
         
         
        /**
         * Calculate the check-digit for any number based on the LUHN algorithm.
         *
         * @author André Fortin <andre.v.fortin@gmail.com>
         *
         * @see http://en.wikipedia.org/wiki/Luhn_algorithm
         */
        private function luhn_checksum( $card_number, $mod = 10 )
        {
                // Remove any non-numeric characters.
                //$card_number = preg_replace("/[^0-9]/", "", (string)$card_number );
                
                $rev_card_number = strrev( (string) $card_number );
                $card_number_checksum = 0;
                
                foreach ( str_split( $rev_card_number ) as $index => $digit )
                { 
                        if( $index % 2 !== 0 )
                        {
                                $card_number_checksum += $digit * 2;
                        } else {
                                $card_number_checksum += $digit;
                        }
                }
                
                return ( $card_number_checksum % $mod );
        }
        
        
        /**
         * Validate a number based on the LUHN algorithm.
         *
         * @author André Fortin <andre.v.fortin@gmail.com>
         *
         */
        private function is_valid_luhn( $card_number, $mod = 10 )
        {
                // Remove any non-numeric characters.
                //$card_number = preg_replace("/[^0-9]/", "", (string)$card_number );
                
                $partial_number = substr( $card_number, 0, strlen( $card_number ) - 1 );
                $check_digit = substr( $card_number, - 1 );

                $checksum = $this->luhn_checksum( $partial_number, $mod );
                
                $result = NULL;
                if( $check_digit == $checksum )
                {
                        $result = true;
                } else {
                        $result = false;
                }
                
                return $result;
        }
        
        
        /**
         * Format a Canadian Social Insurance Number (SIN).
         *
         * @author André Fortin <andre.v.fortin@gmail.com>
         */
        private function format_CA_SIN( $card_number )
        {
                $is_valid        = false;
                $error_code        = 0;
                $location        = '';
                
                // convert to a string and strip any non-numeric characters.
                $card_number = (string)$card_number;
                $card_number = preg_replace("/[^0-9]/", "", $card_number );
                
                $formatted_number  = substr( $card_number, 0, 3 );
                $formatted_number .= '-';
                $formatted_number .= substr( $card_number, 3, 3 );
                $formatted_number .= '-';
                $formatted_number .= substr( $card_number, -3 );
                
                return $formatted_number;
        }
        
        
        /**
         * Generate a valid looking Canadian Social Insurance Number (SIN).
         *
         * The first digit of a SIN indicates province of registration.
         *
         * @author André Fortin <andre.v.fortin@gmail.com>
         *
         */
        private function generate_CA_SIN( $province_code = '' )
        {
                // First digit based on province or registration.
                $sin = '';
                switch ( strtoupper( $province_code ))
                {
                        case 'ON':
                                $sin = (string)rand( 4, 5 );
                                break;
                        case 'QC':
                                $sin = (string)rand( 2, 3 );
                                break;
                        case 'NB':
                        case 'NF':
                        case 'NS':
                        case 'PE':
                                $sin = '1';
                                break;
                        case 'AB':
                        case 'MB':
                        case 'SK':
                        case 'NT':
                        case 'NU':
                                $sin = '6';
                                break;
                        case 'BC':
                        case 'YU':
                                $sin = '7';
                                break;
                        case 'ZZ':
                                $sin = '9';
                                break;
                        default:
                                $sin = (string)rand( 1, 7 );
                                break;
                }
                
                // Add 7 more random digits from 1-9 inclusive.
                for( $digit = 0; $digit < 7; $digit++ )
                {
                        $sin .= rand( 1, 9 );
                }
                
                // Add the check-digit to make it look valid.
                $sin .= $this->luhn_checksum ( $sin );
                
                return $sin;
        }
        
        
        /**
         * Validate a Canadian Social Insurance Number.
         *
         * @author André Fortin <andre.v.fortin@gmail.com>
         */
        private function validate_CA_SIN( $card_number )
        {
                $is_valid        = false;
                $error_code        = 0;
                $location        = '';
                
                // convert to a string and strip any non-numeric characters.
                $card_number = (string)$card_number;
                $card_number = preg_replace("/[^0-9]/", "", $card_number );
                
                // Validate length of the SIN number.
                // Must be 9 digits in length.
                if( self::CA_SIN_VALID_LENGTH == strlen( $card_number ) )
                {
                        // Validate first digit of the SIN.
                        // INVALID if it start with '0' or '8'.
                        $first_digit = substr( $card_number, 0, 1 );
                        if( '0' != $first_digit && '8' != $first_digit )
                        {
                                $is_valid = $this->is_valid_luhn( $card_number, 10 );
                                
                                // Get the province of registration.
                                if( true == $is_valid )
                                {
                                        switch ( substr( $card_number, 0, 1 ) )
                                        {
                                                case '4':
                                                case '5':
                                                        $location = 'ON';
                                                        break;
                                                case '2':
                                                case '3':
                                                        $location = 'QC';
                                                        break;
                                                case '1':
                                                        $location = 'NB,NF,NS,PE';
                                                        break;
                                                case '6':
                                                        $location = 'AB,MB,SK,NT,NU';
                                                        break;
                                                case '7':
                                                        $location = 'BC,YU';
                                                        break;
                                                case '9':
                                                        $location = 'ZZ';
                                                        break;
                                                default:
                                                        $location = '';
                                                        break;
                                        }
                                }
                        } else {
                                $is_valid = false;
                                $error_code = self::CA_SIN_INVALID_CHECKDIGIT;
                        }
                } else {
                        $is_valid = false;
                        $error_code = self::CA_SIN_INVALID_LENGTH;
                }
                
                return array( "success"=>$is_valid, "code"=>$error_code, "id_number"=>$card_number, "location"=>$location );
        }


        
        /**
         * Format a United States Social Security Number (SSN).
         *
         * @author André Fortin <andre.v.fortin@gmail.com>
         */
        private function format_US_SSN( $card_number )
        {
                $is_valid        = false;
                $error_code        = 0;
                $location        = '';
                
                // convert to a string and strip any non-numeric characters.
                $card_number = (string)$card_number;
                $card_number = preg_replace("/[^0-9]/", "", $card_number );
                
                $formatted_number  = substr( $card_number, 0, 3 );
                $formatted_number .= '-';
                $formatted_number .= substr( $card_number, 3, 2 );
                $formatted_number .= '-';
                $formatted_number .= substr( $card_number, -4 );
                
                return $formatted_number;
        }
        
        
        /**
         * Generate a valid looking United States Social Security Number (SSN).
         *
         * The first three digits of an SSN, the area number, indicates location of registration.
         *
         * @author André Fortin <andre.v.fortin@gmail.com>
         *
         */
        private function generate_US_SSN( $state_code = '' )
        {
                $ssn = '';
                
                // Add the 3 digit area number.
                $area_number = '';
                if( '' == $state_code )
                {
                        $area_number = rand( 1, 899 );
                        if( 666 == $area_number )
                        {
                                $area_number = '601';
                        } else {
                                $area_number = '000'.(string)$area_number;
                                $area_number = substr( $area_number, -3 );
                        }
                } else {
                        if(     'AL' == $state_code ) { $area_number = rand( 416, 424 ); }
                        elseif( 'AK' == $state_code ) { $area_number = rand( 574, 574 ); }
                        elseif( 'AR' == $state_code ) { $area_number = rand( 429, 432 ); }
                        elseif( 'AZ' == $state_code )
                        { 
                                $dice_roll = rand( 0, 1 );
                                if( 0 == $dice_roll ) 
                                {
                                        $area_number = rand( 526, 527 );
                                } else { 
                                        $area_number = rand( 600, 601 );
                                }
                        }
                        elseif( 'CA' == $state_code )
                        { 
                                $dice_roll = rand( 0, 1 );
                                if( 0 == $dice_roll ) 
                                {
                                        $area_number = rand( 545, 573 );
                                } else { 
                                        $area_number = rand( 602, 626 );
                                }
                        }
                        elseif( 'CO' == $state_code ) { $area_number = rand( 521, 524 ); }
                        elseif( 'CT' == $state_code ) { $area_number = rand( 040, 049 ); }
                        elseif( 'DC' == $state_code ) { $area_number = rand( 577, 579 ); } 
                        elseif( 'DE' == $state_code ) { $area_number = rand( 221, 222 ); }
                        elseif( 'FL' == $state_code )
                        { 
                                $dice_roll = rand( 0, 1 );
                                if( 0 == $dice_roll ) 
                                {
                                        $area_number = rand( 261, 267 );
                                } else { 
                                        $area_number = rand( 589, 595 );
                                }
                        }
                        elseif( 'GA' == $state_code ) { $area_number = rand( 252, 260 ); }
                        elseif( 'IA' == $state_code ) { $area_number = rand( 478, 485 ); }
                        elseif( 'ID' == $state_code ) { $area_number = rand( 518, 519 ); }
                        elseif( 'IL' == $state_code ) { $area_number = rand( 318, 361 ); }
                        elseif( 'IN' == $state_code ) { $area_number = rand( 303, 317 ); }
                        elseif( 'HI' == $state_code ) { $area_number = rand( 575, 576 ); }
                        elseif( 'KS' == $state_code ) { $area_number = rand( 509, 515 ); }
                        elseif( 'KY' == $state_code ) { $area_number = rand( 400, 407 ); }
                        elseif( 'LA' == $state_code ) { $area_number = rand( 433, 439 ); }
                        elseif( 'MA' == $state_code ) { $area_number = rand( 010, 034 ); }
                        elseif( 'MD' == $state_code ) { $area_number = rand( 212, 220 ); }
                        elseif( 'ME' == $state_code ) { $area_number = rand( 004, 007 ); }
                        elseif( 'MI' == $state_code ) { $area_number = rand( 362, 386 ); }
                        elseif( 'MN' == $state_code ) { $area_number = rand( 468, 477 ); }
                        elseif( 'MO' == $state_code ) { $area_number = rand( 486, 500 ); }
                        elseif( 'MS' == $state_code )
                        { 
                                $dice_roll = rand( 0, 1 );
                                if( 0 == $dice_roll ) 
                                {
                                        $area_number = rand( 425, 428 );
                                } else { 
                                        $area_number = rand( 587, 588 );
                                }
                        }
                        elseif( 'MT' == $state_code ) { $area_number = rand( 516, 517 ); }
                        elseif( 'ND' == $state_code ) { $area_number = rand( 501, 502 ); }
                        elseif( 'NC' == $state_code ) { $area_number = rand( 237, 246 ); }
                        elseif( 'NH' == $state_code ) { $area_number = rand( 001, 003 ); }
                        elseif( 'NJ' == $state_code ) { $area_number = rand( 135, 158 ); }
                        elseif( 'NM' == $state_code )
                        { 
                                $dice_roll = rand( 0, 2 );
                                if( 0 == $dice_roll ) 
                                {
                                        $area_number = rand( 525, 525 );
                                } elseif ( 1== $dice_roll ) { 
                                        $area_number = rand( 585, 585 );
                                } else {
                                        $area_number = rand( 648, 649 );
                                }
                        }
                        elseif( 'NV' == $state_code ) { $area_number = rand( 530, 530 ); }
                        elseif( 'NY' == $state_code ) { $area_number = rand( 050, 134 ); }
                        elseif( 'OH' == $state_code ) { $area_number = rand( 268, 302 ); }
                        elseif( 'OK' == $state_code ) { $area_number = rand( 440, 448 ); }
                        elseif( 'OR' == $state_code ) { $area_number = rand( 540, 544 ); }
                        elseif( 'PA' == $state_code ) { $area_number = rand( 159, 211 ); }
                        elseif( 'PI' == $state_code ) { $area_number = rand( 586, 586 ); } // Pacific Islands = Guam, American Somoa, Philippine Islands, Northern Mariana Islands
                        elseif( 'PR' == $state_code )
                        { 
                                $dice_roll = rand( 0, 1 );
                                if( 0 == $dice_roll ) 
                                {
                                        $area_number = rand( 581, 584 );
                                } else { 
                                        $area_number = rand( 596, 599 ); // Puerto Rico
                                }
                        }
                        elseif( 'RI' == $state_code ) { $area_number = rand( 035, 039 ); }
                        elseif( 'SC' == $state_code ) { $area_number = rand( 247, 251 ); }
                        elseif( 'SD' == $state_code ) { $area_number = rand( 503, 504 ); }
                        elseif( 'NE' == $state_code ) { $area_number = rand( 505, 508 ); }
                        elseif( 'TN' == $state_code ) { $area_number = rand( 408, 415 ); }
                        elseif( 'TX' == $state_code )
                        { 
                                $dice_roll = rand( 0, 1 );
                                if( 0 == $dice_roll ) 
                                {
                                        $area_number = rand( 449, 467 );
                                } else { 
                                        $area_number = rand( 627, 645 );
                                }
                        }
                        elseif( 'UT' == $state_code ) { $area_number = rand( 528, 529 ); }
                        elseif( 'UT' == $state_code ) { $area_number = rand( 646, 647 ); }
                        elseif( 'VA' == $state_code ) { $area_number = rand( 223, 231 ); }
                        elseif( 'VI' == $state_code ) { $area_number = rand( 580, 580 ); } // Virgin Islands
                        elseif( 'VT' == $state_code ) { $area_number = rand( 008, 009 ); }
                        elseif( 'WA' == $state_code ) { $area_number = rand( 531, 539 ); }
                        elseif( 'WI' == $state_code ) { $area_number = rand( 387, 399 ); }
                        elseif( 'WV' == $state_code ) { $area_number = rand( 232, 236 ); }
                        elseif( 'WY' == $state_code ) { $area_number = rand( 520, 520 ); }
                        else {
                                $area_number = rand( 1, 899 );
                                if( 666 == $area_number )
                                {
                                        $area_number = '601';
                                } else {
                                        $area_number = '000'.(string)$area_number;
                                        $area_number = substr( $area_number, -3 );
                                }
                        }
                }
                $ssn .= $area_number;
                
                // Add the 2 digit group code.
                $group_code = rand( 1, 99);
                $group_code = '00'.(string)$group_code;
                $group_code = substr( $group_code, -2 );
                $ssn .= $group_code;
                
                // Add the 4 digit serial number, that is between '0001' and '9999' inclusive.
                $area_number = rand( 1, 9999 );
                $area_number = '0000'.(string)$area_number;
                $area_number = substr( $area_number, -4 );
                $ssn .= $area_number;
                
                return $ssn;
        }
        
        
        /**
         * Validate a United States Social Security Number (SSN).
         *
         * @author André Fortin <andre.v.fortin@gmail.com>
         */
        private function validate_US_SSN( $card_number )
        {
                $is_valid        = false;
                $error_code        = 0;
                $location        = '';
                
                // Convert to a string and strip any non-numeric characters.
                $card_number =  preg_replace("/[^0-9]/", "", (string)$card_number );
                //echo "card_number: ".$card_number."<br/>";
                
                // Validate the length of the SSN, must be 9 digits exactly.
                if( self::US_SSN_VALID_LENGTH == strlen( $card_number ) )
                {
                        // Validate the area number, first 3 digits, of the SSN.
                        // INVALID if the area number, first 3 digits, is '000', or '666', or in the range of '900-999'.
                        $area_number = substr( $card_number, 0, 3 );
                        //echo "area_number: ".$area_number."<br/>";
                        if( 
                                ( '000' != $area_number ) &&
                                ( '666' != $area_number ) &&
                                !( (int)$area_number >= 900 && (int)$area_number <= 999 ) )
                        {
                                // Validate the group code, next 2 digits.
                                $group_code = substr( $card_number, 3, 2 );
                                //echo "group_code: ".$group_code."<br/>";
                                
                                if( '00' != $group_code )
                                {
                                        // Validate the serial number, last 4 digits.
                                        $serial_number = substr( $card_number, -4 );
                                        //echo "serial_number: ".$serial_number."<br/>";
                                        
                                        if( '0000' != $serial_number )
                                        {
                                                $is_valid = true;
                                                
                                                // Return the geographic location of registration.
                                                $area_number = (int)$area_number;                                        

                                                if(     $area_number >= 001 && $area_number <= 003 ) { $location = 'NH'; }
                                                elseif( $area_number >= 004 && $area_number <= 007 ) { $location = 'ME'; }
                                                elseif( $area_number >= 008 && $area_number <= 009 ) { $location = 'VT'; }
                                                elseif( $area_number >= 010 && $area_number <= 034 ) { $location = 'MA'; }
                                                elseif( $area_number >= 035 && $area_number <= 039 ) { $location = 'RI'; }
                                                elseif( $area_number >= 040 && $area_number <= 049 ) { $location = 'CT'; }
                                                elseif( $area_number >= 050 && $area_number <= 134 ) { $location = 'NY'; }
                                                elseif( $area_number >= 135 && $area_number <= 158 ) { $location = 'NJ'; }
                                                elseif( $area_number >= 159 && $area_number <= 211 ) { $location = 'PA'; }
                                                elseif( $area_number >= 212 && $area_number <= 220 ) { $location = 'MD'; }
                                                elseif( $area_number >= 221 && $area_number <= 222 ) { $location = 'DE'; }
                                                elseif( $area_number >= 223 && $area_number <= 231 ) { $location = 'VA'; }
                                                elseif( $area_number >= 232 && $area_number <= 236 ) { $location = 'WV'; }
                                                elseif( $area_number >= 237 && $area_number <= 246 ) { $location = 'NC'; }
                                                elseif( $area_number >= 247 && $area_number <= 251 ) { $location = 'SC'; }
                                                elseif( $area_number >= 252 && $area_number <= 260 ) { $location = 'GA'; }
                                                elseif( $area_number >= 261 && $area_number <= 267 ) { $location = 'FL'; }
                                                elseif( $area_number >= 268 && $area_number <= 302 ) { $location = 'OH'; }
                                                elseif( $area_number >= 303 && $area_number <= 317 ) { $location = 'IN'; }
                                                elseif( $area_number >= 318 && $area_number <= 361 ) { $location = 'IL'; }
                                                elseif( $area_number >= 362 && $area_number <= 386 ) { $location = 'MI'; }
                                                elseif( $area_number >= 387 && $area_number <= 399 ) { $location = 'WI'; }
                                                elseif( $area_number >= 400 && $area_number <= 407 ) { $location = 'KY'; }
                                                elseif( $area_number >= 408 && $area_number <= 415 ) { $location = 'TN'; }
                                                elseif( $area_number >= 416 && $area_number <= 424 ) { $location = 'AL'; }
                                                elseif( $area_number >= 425 && $area_number <= 428 ) { $location = 'MS'; }
                                                elseif( $area_number >= 429 && $area_number <= 432 ) { $location = 'AR'; }
                                                elseif( $area_number >= 433 && $area_number <= 439 ) { $location = 'LA'; }
                                                elseif( $area_number >= 440 && $area_number <= 448 ) { $location = 'OK'; }
                                                elseif( $area_number >= 449 && $area_number <= 467 ) { $location = 'TX'; }
                                                elseif( $area_number >= 468 && $area_number <= 477 ) { $location = 'MN'; }
                                                elseif( $area_number >= 478 && $area_number <= 485 ) { $location = 'IA'; }
                                                elseif( $area_number >= 486 && $area_number <= 500 ) { $location = 'MO'; }
                                                elseif( $area_number >= 501 && $area_number <= 502 ) { $location = 'ND'; }
                                                elseif( $area_number >= 503 && $area_number <= 504 ) { $location = 'SD'; }
                                                elseif( $area_number >= 505 && $area_number <= 508 ) { $location = 'NE'; }
                                                elseif( $area_number >= 509 && $area_number <= 515 ) { $location = 'KS'; }
                                                elseif( $area_number >= 516 && $area_number <= 517 ) { $location = 'MT'; }
                                                elseif( $area_number >= 518 && $area_number <= 519 ) { $location = 'ID'; }
                                                elseif( $area_number >= 520 && $area_number <= 520 ) { $location = 'WY'; }
                                                elseif( $area_number >= 521 && $area_number <= 524 ) { $location = 'CO'; }
                                                elseif( $area_number >= 525 && $area_number <= 525 ) { $location = 'NM'; }
                                                elseif( $area_number >= 526 && $area_number <= 527 ) { $location = 'AZ'; }
                                                elseif( $area_number >= 528 && $area_number <= 529 ) { $location = 'UT'; }
                                                elseif( $area_number >= 530 && $area_number <= 530 ) { $location = 'NV'; }
                                                elseif( $area_number >= 531 && $area_number <= 539 ) { $location = 'WA'; }
                                                elseif( $area_number >= 540 && $area_number <= 544 ) { $location = 'OR'; }
                                                elseif( $area_number >= 545 && $area_number <= 573 ) { $location = 'CA'; }
                                                elseif( $area_number >= 574 && $area_number <= 574 ) { $location = 'AK'; }
                                                elseif( $area_number >= 575 && $area_number <= 576 ) { $location = 'HI'; }
                                                elseif( $area_number >= 577 && $area_number <= 579 ) { $location = 'DC'; }
                                                elseif( $area_number >= 580 && $area_number <= 580 ) { $location = 'VI'; } // Virgin Islands
                                                elseif( $area_number >= 581 && $area_number <= 584 ) { $location = 'PR'; }
                                                elseif( $area_number >= 585 && $area_number <= 585 ) { $location = 'NM'; }
                                                elseif( $area_number >= 586 && $area_number <= 586 ) { $location = 'PI'; } // Pacific Islands = Guam, American Somoa, Philippine Islands, Northern Mariana Islands
                                                elseif( $area_number >= 587 && $area_number <= 588 ) { $location = 'MS'; }
                                                elseif( $area_number >= 589 && $area_number <= 595 ) { $location = 'FL'; }
                                                elseif( $area_number >= 596 && $area_number <= 599 ) { $location = 'PR'; } // Puerto Rico
                                                elseif( $area_number >= 600 && $area_number <= 601 ) { $location = 'AZ'; }
                                                elseif( $area_number >= 602 && $area_number <= 626 ) { $location = 'CA'; }
                                                elseif( $area_number >= 627 && $area_number <= 645 ) { $location = 'TX'; }
                                                elseif( $area_number >= 646 && $area_number <= 647 ) { $location = 'UT'; }
                                                elseif( $area_number >= 648 && $area_number <= 649 ) { $location = 'NM'; }        
                                                else { $location = ''; }
                                                        
                                        } else {
                                                $is_valid = false;
                                                $error_code = self::US_SSN_INVALID_SERIAL_NUMBER;
                                        }
                                } else {
                                        $is_valid = false;
                                        $error_code = self::US_SSN_INVALID_GROUP_CODE;
                                }
                        } else {
                                $is_valid = false;
                                $error_code = self::US_SSN_INVALID_AREA_NUMBER;
                        }
                } else {
                        $is_valid = false;
                        $error_code = self::US_SSN_INVALID_LENGTH;
                }
                
                return array( "success"=>$is_valid, "code"=>$error_code, "id_number"=>$card_number, "location"=>$location );
        }
}

/*
$oUniqueId = new UniqueId();

$result = $oUniqueId->generate( 'CA', 'OHIP' );
$result = $oUniqueId->validate( 'CA', 'OHIP', $result );
echo "OHIP = [" . $oUniqueId->format( 'CA', 'OHIP', '', $result["id_number"] ) . "]<br/>";
var_dump( $result );

$result = $oUniqueId->generate( 'CA', 'SIN', 'QC' );
$result = $oUniqueId->validate( 'CA', 'SIN', $result );
echo "SIN = [" . $oUniqueId->format( 'CA', 'SIN', $result["id_number"] ) . "]<br/>";
var_dump( $result );

$result = $oUniqueId->generate( 'US', 'SSN', 'AZ' );
$result = $oUniqueId->validate( 'US', 'SSN', $result );
echo "SSN = [" . $oUniqueId->format( 'US', 'SSN', $result["id_number"] ) . "]<br/>";
var_dump( $result );

$result = $oUniqueId->generate( 'US', 'SSN', 'PR' );
$result = $oUniqueId->validate( 'US', 'SSN', $result );
echo "SSN = [" . $oUniqueId->format( 'US', 'SSN', $result["id_number"] ) . "]<br/>";
var_dump( $result );
*/
