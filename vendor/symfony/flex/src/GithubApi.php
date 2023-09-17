<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Flex;

use Composer\Util\HttpDownloader;
use Composer\Util\RemoteFilesystem;

class GithubApi
{
    /** @var HttpDownloader|RemoteFilesystem */
    private $downloader;

    public function __construct($downloader)
    {
        $this->downloader = $downloader;
    }

    /**
     * Attempts to find data about when the recipe was installed.
     *
     * Returns an array containing:
     *      commit: The git sha of the last commit of the recipe
     *      date: The date of the commit
     *      new_commits: An array of commit sha's in this recipe's directory+version since the commit
     *                   The key is the sha & the value is the date
     */
    public function findRecipeCommitDataFromTreeRef(string $package, string $repo, string $branch, string $version, string $lockRef): ?array
    {
        $repositoryName = $this->getRepositoryName($repo);
        if (!$repositoryName) {
            return null;
        }

        $recipePath = sprintf('%s/%s', $package, $version);
        $commitsData = $this->requestGitHubApi(sprintf(
            'https://api.github.com/repos/%s/commits?path=%s&sha=%s',
            $repositoryName,
            $recipePath,
            $branch
        ));

        $commitShas = [];
        foreach ($commitsData as $commitData) {
            $commitShas[$commitData['sha']] = $commitData['commit']['committer']['date'];
            // go back the commits one-by-one
            $treeUrl = $commitData['commit']['tree']['url'].'?recursive=true';

            // fetch the full tree, then look for the tree for the package path
            $treeData = $this->requestGitHubApi($treeUrl);
            foreach ($treeData['tree'] as $treeItem) {
                if ($treeItem['path'] !== $recipePath) {
                    continue;
                }

                if ($treeItem['sha'] === $lockRef) {
                    // remove *this* commit from the new commits list
                    array_pop($commitShas);

                    return [
                        // shorten for brevity
                        'commit' => substr($commitData['sha'], 0, 7),
                        'date' => $commitData['commit']['committer']['date'],
                        'new_commits' => $commitShas,
                    ];
                }
            }
        }

        return null;
    }

    public function getVersionsOfRecipe(string $repo, string $branch, string $recipePath): ?array
    {
        $repositoryName = $this->getRepositoryName($repo);
        if (!$repositoryName) {
            return null;
        }

        $url = sprintf(
            'https://api.github.com/repos/%s/contents/%s?ref=%s',
            $repositoryName,
            $recipePath,
            $branch
        );
        $contents = $this->requestGitHubApi($url);
        $versions = [];
        foreach ($contents as $fileData) {
            if ('dir' !== $fileData['type']) {
                continue;
            }

            $versions[] = $fileData['name'];
        }

        return $versions;
    }

    public function getCommitDataForPath(string $repo, string $path, string $branch): array
    {
        $repositoryName = $this->getRepositoryName($repo);
        if (!$repositoryName) {
            return [];
        }

        $commitsData = $this->requestGitHubApi(sprintf(
            'https://api.github.com/repos/%s/commits?path=%s&sha=%s',
            $repositoryName,
            $path,
            $branch
        ));

        $data = [];
        foreach ($commitsData as $commitData) {
            $data[$commitData['sha']] = $commitData['commit']['committer']['date'];
        }

        return $data;
    }

    public function getPullRequestForCommit(string $commit, string $repo): ?array
    {
        $data = $this->requestGitHubApi('https://api.github.com/search/issues?q='.$commit);

        if (0 === \count($data['items'])) {
            return null;
        }

        $repositoryName = $this->getRepositoryName($repo);
        if (!$repositoryName) {
            return null;
        }

        $bestItem = null;
        foreach ($data['items'] as $item) {
            // make sure the PR referenced isn't from a different repository
            if (false === strpos($item['html_url'], sprintf('%s/pull', $repositoryName))) {
                continue;
            }

            if (null === $bestItem) {
                $bestItem = $item;

                continue;
            }

            // find the first PR to reference - avoids rare cases where an invalid
            // PR that references *many* commits is first
            // e.g. https://api.github.com/search/issues?q=a1a70353f64f405cfbacfc4ce860af623442d6e5
            if ($item['number'] < $bestItem['number']) {
                $bestItem = $item;
            }
        }

        if (!$bestItem) {
            return null;
        }

        return [
            'number' => $bestItem['number'],
            'url' => $bestItem['html_url'],
            'title' => $bestItem['title'],
        ];
    }

    private function requestGitHubApi(string $path)
    {
        $contents = $this->downloader->get($path)->getBody();

        return json_decode($contents, true);
    }

    /**
     * Converts the "repo" stored in symfony.lock to a repository name.
     *
     * For example: "github.com/symfony/recipes" => "symfony/recipes"
     */
    private function getRepositoryName(string $repo): ?string
    {
        // only supports public repository placement
        if (0 !== strpos($repo, 'github.com')) {
            return null;
        }

        $parts = explode('/', $repo);
        if (3 !== \count($parts)) {
            return null;
        }

        return implode('/', [$parts[1], $parts[2]]);
    }
}
