<?php



class Retext_Tests_MessageTest extends Docker_Tests_TestCase {
	
	public function testConnect() {

		$connect = new \Docker\ApiConnection();
		$connect->request('get', '/images/json');
		print_r($connect);
		die();

	}
}
