<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class mitsubishi extends eqLogic {

  public static function cron5() {
    if (config::byKey('token', 'melcloud', '') == '') {
      mitsubishi::getToken();
    }
    mitsubishi::refresh();
  }

  /*public static function refresh() {
    $json = mitsubishi::callMelcloud('https://app.melcloud.com/mitsubishi.Wifi.Client/User/ListDevices',array('X-MitsContextKey: ' . config::byKey('token', 'melcloud', '')),array());
    log::add('mitsubishi', 'debug', 'Retrieve ' . print_r($json, true));
  }*/

  public static function getToken() {
    $data = array(
      'Email' => config::byKey('mail', 'mitsubishi'),
      'Password' => config::byKey('password', 'mitsubishi'),
      'Language' => '7',
      'AppVersion' => '1.7.1.0',
      'Persist' => 'true',
      'CaptchaChallenge' => 'null',
      'CaptchaResponse' => 'null'
    );
    $json = mitsubishi::callMelcloud('https://app.melcloud.com/mitsubishi.Wifi.Client/Login/ClientLogin',array(),$data);
    log::add('mitsubishi', 'debug', 'Retrieve ' . print_r($json, true));
    if ($json['ErrorId'] == null) {
      config::save("token", $json['LoginData']['ContextKey'], 'mitsubishi');
    } else {
      log::add('mitsubishi', 'debug', 'Connexion error');
    }

  }

  public static function callMelcloud($_url = '', $_header = array(), $_data = array()) {
    $request_http = new com_http($_url);
    if (!empty($_data)) {
      $request_http->setPost($_data);
    }
    if (!empty($_header)) {
      $request_http->setHeader($_header);
    }
    $output = $request_http->exec(30);
    return json_decode($output, true);
  }

}

class mitsubishiCmd extends cmd {
  public function execute($_options = array()) {
    if ($this->getType() == 'action') {
      $Eqlogic = $this->getEqLogic();
      if ($this->getSubType() == 'slider') {
        $option = $_options['slider'];
      } else {
        $option = $this->getConfiguration('option');
      }
      $Eqlogic->SetModif($option,$this->getConfiguration('flag'),$this->getConfiguration('idflag'));
      mitsubishi::refresh();
    }
  }

}

?>
