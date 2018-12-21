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

class melcloud2 extends eqLogic {

  public static function cron5() {
    if (config::byKey('token', 'melcloud', '') == '') {
      melcloud2::getToken();
    }
    melcloud2::refresh();
  }

  public static function refresh() {
    $json = melcloud2::callMelcloud('https://app.melcloud.com/melcloud2.Wifi.Client/User/ListDevices',array('X-MitsContextKey: ' . config::byKey('token', 'melcloud', '')),array());
    log::add('melcloud2', 'debug', 'Retrieve ' . print_r($json, true));
  }

  public static function getToken() {
    $data = array(
      'Email' => config::byKey('mail', 'melcloud2'),
      'Password' => config::byKey('password', 'melcloud2'),
      'Language' => '7',
      'AppVersion' => '1.7.1.0',
      'Persist' => 'true',
      'CaptchaChallenge' => 'null',
      'CaptchaResponse' => 'null'
    );
    $json = melcloud2::callMelcloud('https://app.melcloud.com/melcloud2.Wifi.Client/Login/ClientLogin',array(),$data);
    log::add('melcloud2', 'debug', 'Retrieve ' . print_r($json, true));
    if ($json['ErrorId'] == null) {
      config::save("token", $json['LoginData']['ContextKey'], 'melcloud2');
    } else {
      log::add('melcloud2', 'debug', 'Connexion error');
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

class melcloud2Cmd extends cmd {
  public function execute($_options = array()) {
    if ($this->getType() == 'action') {
      $Eqlogic = $this->getEqLogic();
      if ($this->getSubType() == 'slider') {
        $option = $_options['slider'];
      } else {
        $option = $this->getConfiguration('option');
      }
      $Eqlogic->SetModif($option,$this->getConfiguration('flag'),$this->getConfiguration('idflag'));
      melcloud2::refresh();
    }
  }

}

?>
