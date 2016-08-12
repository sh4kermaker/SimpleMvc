<?php

namespace SimpleMvc\Model;

class Db
{
	private static $spojenie;
	private static $nastavenie = array(
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        \PDO::ATTR_EMULATE_PREPARES => false,
		);
	
	// inicializuje staticku premnennu $spojenie instanciou triedy \PDO
	public static function pripoj($host, $pouzivatel, $heslo, $databaza) {
        if (!isset(self::$spojenie)) {
                self::$spojenie = @new \PDO(
                        "mysql:host=$host;dbname=$databaza",
                        $pouzivatel,
                        $heslo,
                        self::$nastavenie
                );
        }
	}
	
	// vrati jeden riadok z databazy
	public static function dotazJeden($dotaz, $parametre = array()) {
        $navrat = self::$spojenie->prepare($dotaz);
        $navrat->execute($parametre);
        return $navrat->fetch();
	}
	
	// vrati vsetky vysledky z databazy
	public static function dotazVsetky($dotaz, $parametre = array()) {
        $navrat = self::$spojenie->prepare($dotaz);
        $navrat->execute($parametre);
        return $navrat->fetchAll();
	}
	
	// vrati iba jedinu hodnotu z databazy (napriklad count)
	public static function dotazSamotny($dotaz, $parametre = array()) {
        $vysledok = self::dotazJeden($dotaz, $parametre);
        return $vysledok[0];
	}
	
	// Spustí dotaz a vrátí počet ovplyvnených riadkov
	public static function dotaz($dotaz, $parametre = array()) {
        $navrat = self::$spojenie->prepare($dotaz);
        $navrat->execute($parametre);
        return $navrat->rowCount();
	}

	// pomocou metody dotaz() spusti dotaz na vkladanie udajov do databazy, zabezpecene proti sql injection metodou dotaz() 
	// volanie v tvare - Db::vloz('clanky', $clanok) , druhy parameter je asociativne pole, ktoreho kluce su nazvami atributov v databazovej tabulke
	public static function vloz($tabulka, $parametre = array()) {
        return self::dotaz("INSERT INTO `$tabulka` (`".
                implode('`, `', array_keys($parametre)).
                "`) VALUES (".str_repeat('?,', sizeOf($parametre)-1)."?)",
                        array_values($parametre));
	}
	
	// podobna metoda ako predosla vloz(), volanie v tvare - Db::zmen('clanky', $clanok, 'WHERE `clanky_id` = ?', array(2));
	// $hodnoty je asociativne pole v ktorom kluce su atributy databazovej tabulky, $parametre su hodnoty pre dosadenie do WHERE podmienky
	public static function zmen($tabulka, $hodnoty = array(), $podmienka, $parametre = array()) {
        return self::dotaz("UPDATE `$tabulka` SET `".
                implode('` = ?, `', array_keys($hodnoty)).
                "` = ? " . $podmienka,
                array_merge(array_values($hodnoty), $parametre));
	}
	
	// id posledneho vlozeneho zaznamu
	public static function getLastId()
	{
        return self::$spojenie->lastInsertId();
	}
}