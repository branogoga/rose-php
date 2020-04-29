<?php

namespace Rose\Utils;

class DateTimeHelper
{
    const MIN_DATE = "1970-01-01";
    const MAX_DATE = "2100-01-01";
	
    static public function formatDate(string $tdatum, string $nic): string
    {
        if (substr($tdatum,0,2)=="00")
        {
            return($nic);
        }
        $tempdatum=strtotime($tdatum);
        if($tempdatum === false)
        {
            return $nic;
        }
        $tempdatum=date('d.m.Y',$tempdatum);
        return($tempdatum);
    }

    static public function formatTime(string $tcas, string $nic): string
    {
        if (substr($tcas,0,2)=="00")
        {
            return($nic);
        }
        $tempcas=strtotime($tcas);
        if($tempcas === false)
        {
            return $nic;
        }
        $tempcas=date('H:i:s',$tempcas);
        return($tempcas);
    }

    /// Vrati true, ak zadany den je dnes. Inak vrati false.
    static public function isToday( string $den ): bool
    {
        $dnesnidatum=date("Y-m-d");
        list($dnrok,$dnmes,$dnden)=explode("-",$dnesnidatum);

        list($tstdatum, $tstcas)=explode(" ",$den);
        list($tstrok, $tstmes,$tstden)=explode("-",$tstdatum);

        if(($dnrok==$tstrok) && ($dnmes == $tstmes) && ($dnden == $tstden))
        {
            return true;
        }
        else
        {
            return false;
        }
    }


    // Vrati datum o N dni.
    public static function	nDaysLater( int $n ): string
    {
        if( $n >= 0)
        {
            $t=strtotime("+$n days");
        }
        else
        {
            $t=strtotime("$n days");			
        }

        if($t === false)
        {
            throw new \Exception("Unable to calculate new timestamp.");
        }

        return date("Y-m-d", $t);
    }

    const   NEZALEZI    = -1;
    const   PLAVAJUCI   = 0;
    public static function timeRestriction(int $rok, int $od_do, int $month = null): string
    {
        $dnesnidatum=date("Y-m-d");
        list($dnrok,$dnmes,$dnden)=explode("-",$dnesnidatum);

        $dnrok = intval($dnrok);
        $dnmes = intval($dnmes);
        $dnden = intval($dnden);

        if($rok == self::NEZALEZI):
            $omezujicidatum_od = date("Y-m-d",mktime(0,0,0,1,1,1970));
            $omezujicidatum_do = "2100-12-31"; //date("Y-m-d",mktime(23,59,59,12,31,2100)); 
        elseif ($rok == self::PLAVAJUCI):
            $omezujicidatum_od=date("Y-m-d",mktime(0,0,0,$dnmes,$dnden,$dnrok-1));
            $omezujicidatum_do=date("Y-m-d",mktime(23,59,59,$dnmes,$dnden,$dnrok));;
        else:
            if($month == null)
            {
                $omezujicidatum_od=date("Y-m-d",mktime(0,0,0,1,1,$rok));
                $omezujicidatum_do=date("Y-m-d",mktime(23,59,59,12,31,$rok));
            }
            else
            {
                $omezujicidatum_od=date("Y-m-d",mktime(0,0,0,$month,1,$rok));
                $omezujicidatum_do=date("Y-m-d",mktime(23,59,59,$month,31,$rok));
            }
        endif;

        // finalni upava
        $dnesnidatum=$dnesnidatum." 23:59:59";

        $omezujicidatum_do=$omezujicidatum_do." 23:59:59";
        $omezujicidatum_od=$omezujicidatum_od." 00:00:00";

        if($od_do == 0) 
        {  
            return $omezujicidatum_od;
        } 
        else 
        {
            return $omezujicidatum_do;
        };  
    }

    public static function timeRestrictionFrom(int $rok, int $month = null): string
    { 
        return DateTimeHelper::timeRestriction($rok,0,$month);
    }


    public static function timeRestrictionTo(int $rok, int $month = null): string
    { 
        return DateTimeHelper::timeRestriction($rok,1,$month);
    }	

    public function __construct(){}
    public function __destruct() {}		
}

?>