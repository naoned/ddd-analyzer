<?php

namespace Niktux\DDD\Analyzer\Controllers\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Puzzle\Pieces\PathManipulation;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Controller
{
    use PathManipulation;

    private const
        REPORT_FILE = 'report.json',
        HASH_FILE = 'commit.hash';

    private
        $varPath;

    public function __construct(string $varPath)
    {
        $this->varPath = $varPath;
    }

    public function reportAction(): JsonResponse
    {
        $reportFile = $this->computeFilePath(self::REPORT_FILE);
        $hashFile = $this->computeFilePath(self::HASH_FILE);

        try
        {
            $this->ensureFilesExist([ $reportFile, $hashFile ]);
            $content = $this->retrieveContent($reportFile);
            $content['summary']['hash'] = $this->retrieveHash($hashFile);

            return $this->response($content, JsonResponse::HTTP_OK);
        }
        catch(NotFoundHttpException $exception)
        {
            return $this->response(
                [
                    "error" => $exception->getMessage(),
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }
        catch(\Exception $exception)
        {
            return $this->response(
                [
                    "error" => $exception->getMessage(),
                ],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    private function computeFilePath(string $fileName): string
    {
        return $this->enforceEndingSlash($this->varPath) . $fileName;
    }

    private function ensureFilesExist(array $paths): void
    {
        foreach($paths as $path)
        {
            if(!file_exists($path))
            {
                throw new NotFoundHttpException("Unable to find file at path $path");
            }
        }
    }

    private function retrieveContent(string $path): array
    {
        $content = $this->retrieveFileContent($path);

        $decodedJson = json_decode($content, true);
        if($decodedJson === null)
        {
            throw new \Exception("Unable to decode JSON content of $path file");
        }

        return $decodedJson;
    }

    private function retrieveHash(string $path): string
    {
        return trim($this->retrieveFileContent($path));
    }

    private function retrieveFileContent(string $path): string
    {
        $content = file_get_contents($path);
        if($content === false)
        {
            throw new \Exception("Unable to get content of $path file");
        }

        return $content;
    }

    private function response(?array $content, int $httpReturnCode): JsonResponse
    {
        return new JsonResponse($content, $httpReturnCode);
    }
}
