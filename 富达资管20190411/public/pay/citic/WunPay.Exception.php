<?php
class WunPayException extends Exception {
	public function errorMessage()
	{
		return $this->getMessage();
	}
}
