<?php

require_once sprintf('%s/index.php',dirname(__FILE__));

class User extends \PHPUnit_Framework_TestCase{

	public function testCreate(){

		$resUser = \Twist::User()->create();

		$resUser->firstname('Travis');
		$resUser->surname('CI');
		$resUser->email('travisci@test.com');
		$resUser->password('X123Password');

		$this -> assertEquals(1,$resUser->commit());
	}

	public function testLogin(){

		$arrSessionArray = \Twist::User()->authenticate('travisci@test.com','X123Password');
		$this -> assertEquals(true,$arrSessionArray['status']);
	}

	public function testLoginFail(){

		$arrSessionArray = \Twist::User()->authenticate('travisci@test.com','IncorrectPassword');
		$this -> assertEquals(false,$arrSessionArray['status']);
	}

	public function testEdit(){

		$resUser = \Twist::User()->get(1);
		$resUser->surname('CI_2');

		$this -> assertEquals(true,$resUser->commit());
		unset($resUser);

		$resUser = \Twist::User()->get(1);
		$this -> assertEquals('CI_2',$resUser->surname());
	}

	public function testDisable(){

		$resUser = \Twist::User()->get(1);
		$resUser->disable();

		$this -> assertEquals(true,$resUser->commit());
		unset($resUser);

		$arrSessionArray = \Twist::User()->authenticate('travisci@test.com','X123Password');
		$this -> assertEquals(true,$arrSessionArray['status']);
	}

	public function testDelete(){

		$resUser = \Twist::User()->get(1);
		$this -> assertEquals(true,$resUser->delete());

		$this -> assertEquals(0,count(\Twist::User()->getData(1)));
	}
}