<?php

namespace App\Library;

class Notifications {

	static function recoverCode($email, $code)
	{
		//TODO: from and formated email
		$message = "Para poder restablecer su clave ingrese el código $code en la página de recuperación de acceso.";
		mail($email, 'Recuperación de acceso', $message);
		return true;
	}

}
