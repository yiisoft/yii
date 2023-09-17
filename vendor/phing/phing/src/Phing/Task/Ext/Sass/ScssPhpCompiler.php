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

use Exception;
use Phing\Exception\BuildException;
use Phing\Project;
use ScssPhp\ScssPhp\Compiler;

class ScssPhpCompiler implements SassTaskCompiler
{
    /**
     * @var Compiler
     */
    private $scssCompiler;

    public function __construct(string $style, string $encoding, bool $lineNumbers, string $loadPath)
    {
        $this->scssCompiler = new Compiler();
        if ($style) {
            $ucStyle = ucfirst(strtolower($style));
            $this->scssCompiler->setFormatter('ScssPhp\\ScssPhp\\Formatter\\' . $ucStyle);
        }
        if ($encoding) {
            $this->scssCompiler->setEncoding($encoding);
        }
        if ($lineNumbers) {
            $this->scssCompiler->setLineNumberStyle(1);
        }
        if ($loadPath !== '') {
            $this->scssCompiler->setImportPaths(explode(PATH_SEPARATOR, $loadPath));
        }
    }

    public function compile(string $inputFilePath, string $outputFilePath, bool $failOnError): void
    {
        if (!$this->checkInputFile($inputFilePath, $failOnError)) {
            return;
        }

        $input = file_get_contents($inputFilePath);
        try {
            $out = $this->scssCompiler->compile($input);
            if ($out !== '') {
                $success = file_put_contents($outputFilePath, $out);
                if (!$success && $failOnError) {
                    throw new BuildException(
                        "Cannot write to output file " . var_export($outputFilePath, true),
                        Project::MSG_INFO
                    );
                }
            }
        } catch (Exception $ex) {
            if ($failOnError) {
                throw new BuildException($ex->getMessage());
            }
        }
    }

    private function checkInputFile($inputFilePath, $failOnError): bool
    {
        if (file_exists($inputFilePath) && is_readable($inputFilePath)) {
            return true;
        }

        if ($failOnError) {
            throw new BuildException(
                "Cannot read from input file " . var_export($inputFilePath, true),
                Project::MSG_INFO
            );
        }
        return false;
    }
}
