<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/traits.php';  // General helper functions

// CLASS PresenceDetector
class TwinklyDevice extends IPSModule
{
    use DebugHelper;
    use ProfileHelper;
    use TwinklyHelper;

    // Token constant
    const TOKEN_LIFETIME = 14400;

    // Profil array
    private $assoMODE = [
        [0, 'Aus', '', 0xFF0000, 'off'],
        [1, 'An', '', 0x00FF00, 'movie'],
        [2, 'Demo', '', 0xFF8000, 'demo'],
        [3, 'Echtzeit', '', 0x00FFFF, 'rt'],
    ];

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterPropertyString('Host', '127.0.0.1');

        // Attributes for Login
        $this->RegisterAttributeString('Token', '');
        $this->RegisterAttributeInteger('Validate', 0);

        // Variablen Profile einrichten
        $this->RegisterProfile(VARIABLETYPE_INTEGER, 'Twinkly.Mode', 'Climate', '', '', 0, 0, 0, 0, $this->assoMODE);

        // Variablen erzeugen
        $varID = $this->RegisterVariableInteger('Mode', 'Modus', 'Twinkly.Mode', 0);
        $this->EnableAction('Mode');
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        // IP Check
        $host = $this->ReadPropertyString('Host');
        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            $this->SetStatus(102);
        } else {
            $this->SetStatus(201);
        }
        // Debug message
        $this->SendDebug('ApplyChanges', 'IP=' . $host, 0);
    }

    /**
     * RequestAction - SDK function.
     *
     * @param string $ident Variable identifier
     * @param int $value New value
     */
    public function RequestAction($ident, $value)
    {
        //$this->SendDebug('RequestAction', 'Ident: '.$ident.' Value: '.$value, 0);
        switch ($ident) {
            case 'Mode':
                $this->SendDebug('RequestAction', 'Neuer Modus gewählt: ' . $value, 0);
                $this->CheckLogin();
                $this->SetMode($value);
                SetValue($this->GetIDForIdent($ident), $value);
                break;
            default:
                throw new Exception('Invalid Ident');
        }
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TWICKLY_SetMode($value);
     *
     * @param int $value Mode value.
     */
    public function SetMode($value)
    {
        // Extract assoziated mode string
        $mode = $this->assoMODE[$value][4];
        $this->SendDebug('SetMode', 'Gewählter Modus : ' . $mode, 0);
        // Host
        $host = $this->ReadPropertyString('Host');
        // Token
        $token = $this->ReadAttributeString('Token');
        // Mode
        $set = ['mode' => $mode];
        // Request
        $this->doMode($host, $token, $set);
    }

    /**
     * Validate the token and login to renew it.
     */
    private function CheckLogin()
    {
        // $last =  $this->ReadAttributeInteger('Validate');
        $now = time();
        //if($now - $livetime > $last) {
        if (true) {
            // Timestamp
            $this->WriteAttributeInteger('Validate', $now);
            // Host
            $host = $this->ReadPropertyString('Host');
            // Login & Validate
            $challange = $this->doLogin($host);
            $token = $challange['authentication_token'];
            $response = $challange['challenge-response'];
            $this->doVerify($host, $token, $response);
            // Token
            $this->WriteAttributeString('Token', $token);
        }
    }
}
