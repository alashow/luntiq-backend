<?php

namespace App\Util\Downloader;

use Log;

class Aria2Client
{
    protected $jsonRpcClient;

    protected $token;

    protected $defaultParams = [
        'allow-overwrite'           => 'true',
        'auto-file-renaming'        => 'false',
        'file-allocation'           => 'none',
        'max-connection-per-server' => '6',
        'split'                     => '6',
    ];

    protected $premUrlPattern = '/(https:\/\/[a-zA-Z]{1,40})(-)(sng1|fra1|nyc1|sfo1|tor1|sto)(.*)/';
    protected $premCdnLocations = ['sng1', 'fra1', 'nyc1', 'sfo1', 'tor1', 'sto'];

    /**
     * Aria2Client constructor.
     */
    public function __construct()
    {
        $this->jsonRpcClient = \Graze\GuzzleHttp\JsonRpc\Client::factory(sprintf('%s/jsonrpc', env('DOWNLOADS_ARIA2_HOST')));
        $this->token = env('DOWNLOADS_ARIA2_TOKEN');
    }

    /**
     * Trim aria2 jsonrpc response to internal response.
     *
     * @param array    $responses
     * @param callable $key
     *
     * @return array
     */
    private function transformResponses($responses, callable $key = null)
    {
        $result = [];
        $count = count($responses);
        for ($i = 0; $i < $count; $i++) {
            $response = json_decode((string) $responses[$i]->getBody());
            if (isset($response->result)) {
                if ($key == null) {
                    $result[] = $response->result;
                } else {
                    $result[$key($i)] = $response->result;
                }
            }
        }

        return $result;
    }

    /**
     * @param array $files
     *                     key - return key for value as key
     *                     folder - directory path to download to
     *                     name - file name to save as
     *                     url - url to download
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.addUri
     * @return array array of started downloads, given file key as key and value as download GID.
     */
    public function download(array $files)
    {
        $requests = [];
        foreach ($files as $file) {
            Log::info('Adding file to download queue', $file);

            $uris = $this->verifyAndMultiplyUrl($file['url']);
            $requests[] = $this->jsonRpcClient->request(1, 'aria2.addUri', [
                "token:{$this->token}", $uris,
                [
                    'dir' => $file['folder'],
                    'out' => $file['name'],
                ] + $this->defaultParams,
            ]);
        }
        $responses = $this->jsonRpcClient->sendAll($requests);

        return $this->transformResponses($responses, function ($index) use ($files) {
            return $files[$index]['key'];
        });
    }

    /**
     * @param $ids GID's of downloads to check.
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.tellStatus
     * @return array result
     */
    public function checkStatus($ids)
    {
        $requests = [];
        foreach ($ids as $id) {
            $requests[] = $this->jsonRpcClient->request(1, 'aria2.tellStatus', [
                "token:{$this->token}", $id,
            ]);
        }

        $responses = $this->jsonRpcClient->sendAll($requests);

        return $this->transformResponses($responses);
    }

    /**
     * @param $ids GID's of downloads to cancel.
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.remove
     * @return array result
     */
    public function cancel($ids)
    {
        $requests = [];
        foreach ($ids as $id) {
            $requests[] = $this->jsonRpcClient->request(1, 'aria2.remove', [
                "token:{$this->token}", $id,
            ]);
        }

        $responses = $this->jsonRpcClient->sendAll($requests);

        return $this->transformResponses($responses);
    }

    /**
     * If url matches premiumize cdn url format, then this function generates an url to the file for multiple cdn location.
     * Otherwise, it just returns given url.
     *
     * @param string|array $url
     *
     * @return array|string
     */
    private function verifyAndMultiplyUrl($url)
    {
        if (is_string($url)) {
            preg_match($this->premUrlPattern, $url, $matches);
            if (! empty($matches)) {
                $urls = [];
                foreach ($this->premCdnLocations as $location) {
                    $urls[] = preg_replace($this->premUrlPattern, "$1$2$location$4", $url);
                }
                return $urls;
            }
            return [$url];
        } else {
            return $url;
        }
    }
}