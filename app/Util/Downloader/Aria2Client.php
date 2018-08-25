<?php

namespace App\Util\Downloader;


use Closure;

class Aria2Client
{
    protected $jsonRpcClient;

    protected $token;

    protected $defaultParams = [
        'allow-overwrite' => 'true',
        'auto-file-renaming'  => 'false',
    ];

    /**
     * Aria2Client constructor.
     */
    public function __construct()
    {
        $this->jsonRpcClient = \Graze\GuzzleHttp\JsonRpc\Client::factory(sprintf('%s/jsonrpc', env('DOWNLOADS_ARIA2_HOST')));
        $this->token = env('DOWNLOADS_ARIA2_TOKEN');
    }

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
            $uris = is_string($file['url']) ? [$file['url']] : $file['url'];
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
}