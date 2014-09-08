<?php

/**
 * EOS Static logging facade
 *
 * Do not forget invoke EosStaticFacade::init() before usage!
 */
class EosStaticFacade
{
    private static $realm  = "";
    private static $secret = "";
    private static $host   = "";
    private static $port   = "";

    private static $trackingKey = null;

    private static $socket = null;

    /**
     * Initializes EOS logging facade
     *
     * @param string $host   Hostname or IP (ip is preferred)
     * @param int    $port   Port. If omitted, or empty provided, 8087 is used
     * @param string $realm  Realm name
     * @param string $secret Realm secret
     */
    public static function init($host, $port, $realm, $secret)
    {
        self::$host   = $host;
        self::$port   = empty($port) ? 8087 : $port;
        self::$realm  = $realm;
        self::$secret = $secret;

        self::$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        self::$trackingKey = crc32(php_uname() . gethostname()) . '.' . microtime(true);
    }

    /**
     * Performs logging of provided content
     *
     * @param mixed           $content Content to log
     * @param string|string[] $tags    List of tags to use
     */
    public static function log($content, $tags)
    {
        if (self::$socket === null) {
            // No initialized
            return;
        }

        if (!is_array($tags)) {
            $tags = array($tags);
        }

        if (is_array($content) || is_object($content)) {
            $content = json_encode($content);
        }

        // Validating
        if (empty(self::$realm) || empty($tags) || empty($content) || empty(self::$host)) {
            return;
        }

        // Creating packet and signature
        $nonce  = microtime(true) . mt_rand();
        $hash   = hash("sha256", $nonce . $content . self::$secret);
        $packet = $nonce . "\n" . self::$realm . "+" . $hash . "\nlog://" . implode(':', $tags) . "\n" . $content;

        socket_sendto(self::$socket, $packet, strlen($packet), 0, self::$host, self::$port);
    }

    /**
     * Rich log
     *
     * @param mixed           $content
     * @param string|string[] $tags
     */
    public static function richLog($content, $tags)
    {
        if ($content instanceof Exception) {
            $content = array(
                'exception' => array(
                    'message' => $content->getMessage(),
                    'code'    => $content->getCode(),
                    'file'    => $content->getFile(),
                    'line'    => $content->getLine(),
                    'trace'   => $content->getTrace()
                )
            );
        } elseif (!is_array($content)) {
            $content = array(
                'message' => '' . $content
            );
        }

        // Boxing tags
        if (empty($tags)) {
            $tags = array();
        } else if (!is_array($tags)) {
            $tags = array($tags);
        }

        // Adding hostname as tag
        $tags[] = gethostname();

        if (isset($content['tags'])) {
            if (is_string($content['tags'])) {
                $tags[] = array($content['tags']);
            }
            if (is_array($content['tags'])) {
                $tags = array_merge($content['tags'], $tags);
            }
            unset($content['tags']);
        }

        $content['eos-id'] = self::$trackingKey;

        self::log($content, $tags);
    }
}