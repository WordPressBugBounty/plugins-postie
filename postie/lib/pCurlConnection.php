<?php
// phpcs:ignoreFile

class pCurlConnection extends pConnection {

    private $buffer = array();

    public function __construct($type, $host, $username, $password, $port = NULL, $secure = FALSE, $timeout = NULL) {
        parent::__construct($type, $host, $username, $password, $port, $secure, $timeout);
        DebugEcho("Connecting via cURL");
        DebugDump(curl_version());
    }

    public function close() {
        
    }

    public function open() {
        
    }

    public function write($command, $expected = null) {
        $suffix = '';
        if ($this->type == 'imap') {
            if ($command == 'LOGOUT') {
                return;
            }

            $suffix = '/' . $this->mailbox;
        }

        $url = ($this->type == 'imap' ? 'imap' : 'pop3') . ($this->secure ? 's' : '') . '://' . $this->host . $suffix;

        DebugEcho("curl write: $url - $command");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERPWD, "$this->username:$this->password");
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PORT, $this->port);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_NOBODY, true);

        $fp = tmpfile();
        curl_setopt($ch, CURLOPT_FILE, $fp);
//        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($cp, $data) use ($fp) {
//            echo "curl cb: $data\n";
//            return fwrite($fp, $data);
//        });

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $command);

        $res = curl_exec($ch);

        DebugEcho("curl return: $res");

        rewind($fp);
        while (!feof($fp)) {
            $buffer = fgets($fp);
            echo "curl: $buffer";
        }
        fclose($fp);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);
        DebugEcho("curl log: $verboseLog");
        fclose($verbose);

        if (false === $res) {
            DebugEcho("Curl: error - " . curl_error($ch) . ' (' . curl_errno($ch) . ')');
            throw new fConnectivityException('Unable to write data to %1$s server %2$s on port %3$s', strtoupper($this->type), $this->host, $this->port);
        } else {
            $res = explode("\r\n", $verboseLog);
            $res = array_filter($res, array(__CLASS__, 'popoutput'));
            array_walk($res, array(__CLASS__, 'first2'));
            $this->buffer = array_merge($this->buffer, $res);
        }
        curl_close($ch);

        //DebugDump($this->buffer);

        return $this->read($expected);
    }

    function popoutput($l) {
        return strlen($l) > 1 && substr($l, 0, 2) == '< ';
    }

    function first2(&$l) {
        $l = substr($l, 2);
    }

    public function read($expect = NULL) {
        $response = array();
        while (count($this->buffer) > 0) {
            $line = array_shift($this->buffer);

            $response[] = $line;
            //DebugEcho("curl read: $line");
            // Automatically stop at the termination octet or a bad response
            if ($this->type == 'pop3' && (count($response) == 1 && $response[0][0] == '-')) {
                break;
            }

            if ($expect !== NULL) {
                $matched_number = is_int($expect) && sizeof($response) == $expect;
                $matched_regex = is_string($expect) && preg_match($expect, $line);
                if ($matched_number || $matched_regex) {
                    DebugEcho("curl read: matched expect");
                    break;
                }
            }
        }

        if ($this->type == 'pop3') {
            // Remove the termination octet
            if ($response && $response[sizeof($response) - 1] == '.') {
                $response = array_slice($response, 0, -1);
            }
            // Remove byte-stuffing
            $lines = count($response);
            for ($i = 0; $i < $lines; $i++) {
                if (strlen($response[$i]) && $response[$i][0] == '.') {
                    $response[$i] = substr($response[$i], 1);
                }
            }
        }

        $d = array_slice($response, -1);
        DebugEcho("curl read (" . count($response) . ') :' . array_pop($d));
        return $response;
    }

    public function isPersistant() {
        return false;
    }

    public function buildFetchUrl($uid) {
        if ($this->type == 'imap') {
            $mailbox = trim($this->mailbox, '/');
            if ($mailbox === '') {
                $mailbox = 'INBOX';
            }
            return ($this->secure ? 'imaps' : 'imap') . '://' . $this->host . '/' . $mailbox . '/;UID=' . $uid;
        }

        return ($this->secure ? 'pop3s' : 'pop3') . '://' . $this->host . '/' . $uid;
    }

    public function fetchMessageSource($uid) {
        $url = $this->buildFetchUrl($uid);
        return $this->executeFetch($url, $uid);
    }

    protected function executeFetch($url, $uid) {
        DebugEcho("curl fetch: $url");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERPWD, "$this->username:$this->password");
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PORT, $this->port);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        $fp = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) use ($fp) {
            return fwrite($fp, $data);
        });

        $headers = array();
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($ch, $header) use (&$headers) {
            $headers[] = $header;
            return strlen($header);
        });

        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $res = curl_exec($ch);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);
        DebugEcho("curl log: $verboseLog");
        fclose($verbose);

        if (false === $res) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            DebugEcho("Curl fetch error: $error ($errno)");
            fclose($fp);
            curl_close($ch);
            throw new fConnectivityException('Unable to fetch message %1$s from %2$s server %3$s on port %4$s: %5$s', $uid, strtoupper($this->type), $this->host, $this->port, $error);
        }

        curl_close($ch);

        rewind($fp);
        $message = stream_get_contents($fp);
        fclose($fp);

        return $message;
    }
}
