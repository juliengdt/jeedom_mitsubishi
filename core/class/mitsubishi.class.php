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
    if (config::byKey('MyToken', 'melcloud', '') == '') {
      mitsubishi::getToken();
    }
    mitsubishi::refresh();
  }

  public static function refresh() {
    $json = mitsubishi::callMelcloud('https://app.melcloud.com/Mitsubishi.Wifi.Client/User/ListDevices',array('X-MitsContextKey: ' . config::byKey('MyToken', 'melcloud', '')),array());
    log::add('mitsubishi', 'debug', 'Retrive ' . print_r($json, true));
  }

  public function SetModif($option,$flag,$idflag){

  }

  public static function getToken() {
    $data = array(
      'Email' => config::byKey('MyEmail', 'mitsubishi'),
      'Password' => config::byKey('MyPassword', 'mitsubishi'),
      'Language' => '7',
      'AppVersion' => '1.7.1.0',
      'Persist' => 'true',
      'CaptchaChallenge' => 'null',
      'CaptchaResponse' => 'null'
    );
    $json = mitsubishi::callMelcloud('https://app.melcloud.com/Mitsubishi.Wifi.Client/Login/ClientLogin',array(),$data);

    if ($json['ErrorId'] == null) {
      log::add('mitsubishi', 'debug', 'Login ok ');
      config::save("MyToken", $json['LoginData']['ContextKey'], 'mitsubishi');
    } else {
      log::add('mitsubishi', 'debug', 'Login ou mot de passe Melcloud incorrecte.');
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
    $request->setHeader(array('X-MitsContextKey: ' . $montoken));
    $output = $request_http->exec(30);
    return json_decode($output, true);
  }

  public function loadCmdFromConf($type) {
		if (!is_file(dirname(__FILE__) . '/../config/devices/' . $type . '.json')) {
			return;
		}
		$content = file_get_contents(dirname(__FILE__) . '/../config/devices/' . $type . '.json');
		if (!is_json($content)) {
			return;
		}
		$device = json_decode($content, true);
		if (!is_array($device) || !isset($device['commands'])) {
			return true;
		}
		foreach ($device['commands'] as $command) {
			$cmd = null;
			foreach ($this->getCmd() as $liste_cmd) {
				if ((isset($command['logicalId']) && $liste_cmd->getLogicalId() == $command['logicalId'])
				|| (isset($command['name']) && $liste_cmd->getName() == $command['name'])) {
					$cmd = $liste_cmd;
					break;
				}
			}
			if ($cmd == null || !is_object($cmd)) {
				$cmd = new shellyCmd();
				$cmd->setEqLogic_id($this->getId());
				utils::a2o($cmd, $command);
				$cmd->save();
			}
		}
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
