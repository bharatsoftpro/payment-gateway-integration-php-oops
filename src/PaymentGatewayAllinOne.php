<?php

/**
 * Interface StandardPaymentService
 */
interface StandardPaymentService
{
    public function pay();
}

/**
 * Interface FraudCheckService
 */
interface FraudCheckService
{
    public function fraudCheck();
}

/**
 * Interface ThreeDSecureCheckService
 */
interface ThreeDSecureCheckService
{
    public function threeDcheck();
}

/**
 * Interface PaymentProcessService
 */
interface PaymentProcessService
{
    public function process();
}

/**
 * Class PaymentException
 */
class PaymentException extends Exception
{
}

/**
 * Class StandardPayment, its property set by requested payment gateway or its class
 */
class StandardPayment
{
    private $transId;
    private $amt;
    private $currencyType;
    private $endPointUrl;
    private $returnEndPointUrl;
    private $notifyEndPointUrl;
    private $submitMethod;
    private $merchantKey;
    private $privateKey;
    private $publicKey;

    /**
     * StandardPayment constructor.
     * @param array $attributes
     */
    public function __construct($attributes = Array())
    {
        //Apply provided attribute values
        foreach ($attributes as $attribute => $value) {
            $this->$attribute = $value;
        }
    }

    /**
     * Getter/Setter not defined so set as property of object
     * @param $name
     * @param $value
     */
    function __set($name, $value)
    {
        if (method_exists($this, $name)) {
            $this->$name($value);
        } else {

            $this->$name = $value;
        }
    }

    /**
     * Getter/Setter not defined so return property if it exists
     * @param $name
     * @return null
     */
    function __get($name)
    {
        if (method_exists($this, $name)) {
            return $this->$name();
        } elseif (property_exists($this, $name)) {

            return $this->$name;
        }
        return null;
    }
}

/**
 * Class AbcPay
 */
class AbcPay extends StandardPayment implements StandardPaymentService, PaymentProcessService
{

//    private $endPointUrl = "http://abc.com/pay";

    /**
     * AbcPay constructor.
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
    }

    /**
     * @return AbcPay
     */
    public function process()
    {
        return $this->pay();

    }

    /**
     * @return $this
     */
    public function pay()
    {
        return $this;
    }

}


/**
 * Class XyzPay
 */
class XyzPay extends StandardPayment implements StandardPaymentService, PaymentProcessService, ThreeDSecureCheckService
{

    /**
     * XyzPay constructor.
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
    }

    /**
     * @return $this
     */
    public function pay()
    {
        return $this;
    }

    /**
     * @return $this
     */
    public function threeDcheck()
    {
        return $this;
    }

    /**
     * @return $this
     */
    public function process()
    {
        $this->threeDcheck();
        $this->pay();

        return $this;
    }

}

/**
 * Class PaymentGateway
 */
class PaymentGateway
{
    /**
     * DI based on payment gateway
     * @param PaymentProcessService $paymentProcessService
     * @return mixed
     */
    public function takePayment(PaymentProcessService $paymentProcessService)
    {
        debug($paymentProcessService);
        return $paymentProcessService->process();
    }
}

/**
 * Class EtcPay
 */
class EtcPay extends StandardPayment implements StandardPaymentService, PaymentProcessService, FraudCheckService
{
    /**
     * EtcPay constructor.
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * @return $this
     * @throws PaymentException
     */
    public function fraudCheck()
    {
        //Dummy check
        if (!$this->transId) {
            throw new PaymentException("id required");
        }
        if ($this->amt <= 0) {
            throw new PaymentException("amt required");
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function pay()
    {
        return $this;
    }

    /**
     * @return $this
     */
    public function process()
    {
        $this->fraudCheck();
        $this->pay();

        return $this;
    }
}

//TEST and todo: make db call for specific payment gateway & its setting from db.
try {
    $paymentGateway = new PaymentGateway();
    $paymentGateway->takePayment(new AbcPay());
    //pass the data from db
    // $paymentGateway->takePayment(new AbcPay(['endPointUrl' => 'http://abc.com/pay', 'additionPropertyBasedOnPaymentGateway'=>'its value']));
    $paymentGateway->takePayment(new XyzPay());
    $paymentGateway->takePayment(new EtcPay());

} catch (PaymentException $e) {
    debug($e->getMessage());
} finally {
    debug("Thank you :)");
}

/**
 * Utility method for Print or debug
 * @param array ...$var
 */
function debug(...$var)
{
    echo "<pre>";
    var_dump($var);
}