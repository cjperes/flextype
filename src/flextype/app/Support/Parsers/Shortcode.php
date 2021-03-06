<?php

declare(strict_types=1);

/**
 * Flextype (https://flextype.org)
 * Founded by Sergey Romanenko and maintained by Flextype Community.
 */

namespace Flextype\App\Support\Parsers;

use function md5;

class Shortcode
{
    /**
     * Flextype Dependency Container
     */
    private $flextype;

    /**
     * Shortcode Fasade
     */
    private $shortcode;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct($flextype, $shortcode)
    {
        $this->flextype  = $flextype;
        $this->shortcode = $shortcode;
    }

    /**
     * Add shortcode handler
     *
     * @param string   $name    Shortcode name
     * @param callable $handler Handler
     */
    public function add(string $name, $handler)
    {
        return $this->shortcode->addHandler($name, $handler);
    }

    /**
     * Takes a SHORTCODE encoded string and converts it into a PHP variable.
     *
     * @param string $input A string containing SHORTCODE
     * @param bool   $cache Cache result data or no. Default is true
     *
     * @return mixed The SHORTCODE converted to a PHP value
     */
    public function parse(string $input, bool $cache = true) : string
    {
        if ($cache === true && $this->flextype['registry']->get('flextype.settings.cache.enabled') === true) {
            $key = $this->getCacheID($input);

            if ($data_from_cache = $this->flextype['cache']->fetch($key)) {
                return $data_from_cache;
            }

            $data = $this->_parse($input);
            $this->flextype['cache']->save($key, $data);

            return $data;
        }

        return $this->_parse($input);
    }

    /**
     * @see parse()
     */
    protected function _parse(string $input) : string
    {
        return $this->shortcode->process($input);
    }

    protected function getCacheID($input)
    {
        return md5('shortcode' . $input);
    }
}
