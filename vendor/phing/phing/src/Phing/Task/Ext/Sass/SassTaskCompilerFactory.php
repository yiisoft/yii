<?php

/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

declare(strict_types=1);

namespace Phing\Task\Ext\Sass;

use Phing\Exception\BuildException;
use Phing\Io\FileSystem;

class SassTaskCompilerFactory
{
    /**
     * @var FileSystem
     */
    private $fs;

    public function __construct(FileSystem $fs)
    {
        $this->fs = $fs;
    }

    public function prepareCompiler(SassTask $sassTask): SassTaskCompiler
    {
        $this->assertCompilerIsSet($sassTask);

        // If both are set to be used, prefer sass over scssphp.
        if ($sassTask->getUseSass() && $sassTask->getUseScssPhp()) {
            if ($this->fs->which($sassTask->getExecutable()) === false) {
                $this->assertScssPhpIsAvailable();
                return new ScssPhpCompiler(
                    $sassTask->getStyle(),
                    $sassTask->getEncoding(),
                    $sassTask->getLineNumbers(),
                    $sassTask->getPath()
                );
            }
        } elseif ($sassTask->getUseSass()) {
            $this->assertSassIsAvailable($sassTask);
        } elseif ($sassTask->getUseScssPhp()) {
            $this->assertScssPhpIsAvailable();
            return new ScssPhpCompiler(
                $sassTask->getStyle(),
                $sassTask->getEncoding(),
                $sassTask->getLineNumbers(),
                $sassTask->getPath()
            );
        }

        return new SassCompiler($sassTask->getExecutable(), $sassTask->getFlags());
    }

    private function assertCompilerIsSet(SassTask $sassTask): void
    {
        if (!$sassTask->getUseSass() && !$sassTask->getUseScssPhp()) {
            throw new BuildException("Neither sass nor scssphp are to be used.");
        }
    }

    private function assertScssPhpIsAvailable(): void
    {
        if (!$this->isScssPhpLoaded()) {
            $msg = sprintf(
                "Install scssphp/scssphp."
            );
            throw new BuildException($msg);
        }
    }

    private function assertSassIsAvailable(SassTask $sassTask): void
    {
        if ($this->fs->which($sassTask->getExecutable()) === false) {
            $msg = sprintf(
                "%s not found. Install sass.",
                $sassTask->getExecutable()
            );
            throw new BuildException($msg);
        }
    }

    private function isScssPhpLoaded(): bool
    {
        return class_exists('\ScssPhp\ScssPhp\Compiler');
    }
}
