<?php

namespace Lightningstrike\Trait;

trait ReadsArrayPaths
{
    /**
     * @param array<int|string,mixed> $searchable
     */
    private function getByPathFromArray(string $path, array $searchable): mixed
    {
        if ($searchable === []) {
            return null;
        }

        $pathSegments = explode('.', $path);

        foreach ($pathSegments as $pathSegment) {
            if (!is_array($searchable)) {
                return null;
            }

            if (array_key_exists($pathSegment, $searchable)) {
                $searchable = $searchable[$pathSegment];
                continue;
            } elseif (is_numeric($pathSegment) && array_key_exists((int)$pathSegment, $searchable)) {
                $searchable = $searchable[(int)$pathSegment];
                continue;
            } else {
                return null;
            }
        }

        return $searchable;
    }
}
