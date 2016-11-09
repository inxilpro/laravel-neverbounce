<?php

namespace Groundsix\Neverbounce;

use Log;
use Illuminate\Support\Facades\Cache;
use NeverBounce\API\NB_Single;
use NeverBounce\API\NB_Exception;

class NeverBounce
{
    /**
     * @var NB_Single
     */
    protected $app;

    /**
     * @var string|array
     */
    protected $validResults;

    /**
     * @var bool
     */
    protected $cacheEnabled;

    /**
     * @var int
     */
    protected $cacheExpiration;

    /**
     * Constructor
     *
     * @param NB_Single $single
     * @param string|array $validResults
     * @param bool $cacheEnabled
     * @param int $cacheExpiration
     */
    public function __construct(NB_Single $single, $validResults = 'valid', $cacheEnabled = false, $cacheExpiration = 1440)
    {
        $this->app = $single;
        $this->validResults = $validResults;
        $this->cacheEnabled = $cacheEnabled;
        $this->cacheExpiration = $cacheExpiration;
    }

    /**
     * Validates an email address against neverbounce.com.
     *
     * @param string $email The email in question
     *
     * @return bool
     */
    public function valid($email)
    {
        if ($this->cacheEnabled) {
            $cacheKey = static::class.'@valid('.$email.')';
            $cached = Cache::get();
            if (null !== $cached && is_bool($cached)) {
                return $cached;
            }
        }

        try {
            $valid = $this->app->verify($email)->is($this->validResults);
            if ($this->cacheEnabled) {
                Cache::put($cacheKey, $valid, $this->cacheExpiration);
            }
        } catch (NB_Exception $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            $valid = false;
        }

        return $valid;
    }
}
