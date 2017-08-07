<?php
namespace Payments\Wxpay;

class  SDKRuntimeException extends \Exception 
{
	public function errorMessage()
	{
		return $this->getMessage();
	}
}
?>