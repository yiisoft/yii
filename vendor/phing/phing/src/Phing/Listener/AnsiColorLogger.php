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

namespace Phing\Listener;

use Phing\Io\File;
use Phing\Io\IOException;
use Phing\Io\OutputStream;
use Phing\Phing;
use Phing\Project;
use Phing\Util\Properties;

/**
 * Uses ANSI Color Code Sequences to colorize messages
 * sent to the console.
 *
 * If used with the -logfile option, the output file
 * will contain all the necessary escape codes to
 * display the text in colorized mode when displayed
 * in the console using applications like cat, more,
 * etc.
 *
 * This is designed to work on terminals that support ANSI
 * color codes.  It works on XTerm, ETerm, Mindterm, etc.
 * It also works on Win9x (with ANSI.SYS loaded.)
 *
 * NOTE:
 * It doesn't work on WinNT's COMMAND.COM even with
 * ANSI.SYS loaded.
 *
 * The default colors used for differentiating
 * the message levels can be changed by editing the
 * etc/default.listeners.properties file.
 *
 * This file contains 5 key/value pairs:
 * AnsiColorLogger.ERROR_COLOR=2;31
 * AnsiColorLogger.WARNING_COLOR=2;35
 * AnsiColorLogger.INFO_COLOR=2;36
 * AnsiColorLogger.VERBOSE_COLOR=2;32
 * AnsiColorLogger.DEBUG_COLOR=2;34
 *
 * Another option is to pass a system variable named
 * ant.logger.defaults, with value set to the path of
 * the file that contains user defined Ansi Color
 * Codes, to the <B>java</B> command using -D option.
 *
 * To change these colors use the following chart:
 *
 *      <B>ANSI COLOR LOGGER CONFIGURATION</B>
 *
 * Format for AnsiColorLogger.*=
 *  Attribute;Foreground;Background
 *
 *  Attribute is one of the following:
 *  0 -> Reset All Attributes (return to normal mode)
 *  1 -> Bright (Usually turns on BOLD)
 *  2 -> Dim
 *  3 -> Underline
 *  5 -> link
 *  7 -> Reverse
 *  8 -> Hidden
 *
 *  Foreground is one of the following:
 *  30 -> Black
 *  31 -> Red
 *  32 -> Green
 *  33 -> Yellow
 *  34 -> Blue
 *  35 -> Magenta
 *  36 -> Cyan
 *  37 -> White
 *
 *  Background is one of the following:
 *  40 -> Black
 *  41 -> Red
 *  42 -> Green
 *  43 -> Yellow
 *  44 -> Blue
 *  45 -> Magenta
 *  46 -> Cyan
 *  47 -> White
 *
 * @author  Hans Lellelid <hans@xmpl.org> (Phing)
 * @author  Magesh Umasankar (Ant)
 */
class AnsiColorLogger extends DefaultLogger
{
    public const ATTR_NORMAL = 0;
    public const ATTR_BRIGHT = 1;
    public const ATTR_DIM = 2;
    public const ATTR_UNDERLINE = 3;
    public const ATTR_BLINK = 5;
    public const ATTR_REVERSE = 7;
    public const ATTR_HIDDEN = 8;

    public const FG_BLACK = 30;
    public const FG_RED = 31;
    public const FG_GREEN = 32;
    public const FG_YELLOW = 33;
    public const FG_BLUE = 34;
    public const FG_MAGENTA = 35;
    public const FG_CYAN = 36;
    public const FG_WHITE = 37;

    public const BG_BLACK = 40;
    public const BG_RED = 41;
    public const BG_GREEN = 42;
    public const BG_YELLOW = 44;
    public const BG_BLUE = 44;
    public const BG_MAGENTA = 45;
    public const BG_CYAN = 46;
    public const BG_WHITE = 47;

    public const PREFIX = "\x1b[";
    public const SUFFIX = 'm';
    public const SEPARATOR = ';';
    public const END_COLOR = "\x1b[0m"; // self::PREFIX . self::SUFFIX;

    private $errColor;
    private $warnColor;
    private $infoColor;
    private $verboseColor;
    private $debugColor;

    private $colorsSet = false;

    /**
     * Construct new AnsiColorLogger
     * Perform initializations that cannot be done in var declarations.
     */
    public function __construct()
    {
        parent::__construct();
        $this->errColor = self::PREFIX . self::ATTR_NORMAL . self::SEPARATOR . self::FG_RED . self::SUFFIX;
        $this->warnColor = self::PREFIX . self::ATTR_NORMAL . self::SEPARATOR . self::FG_MAGENTA . self::SUFFIX;
        $this->infoColor = self::PREFIX . self::ATTR_NORMAL . self::SEPARATOR . self::FG_CYAN . self::SUFFIX;
        $this->verboseColor = self::PREFIX . self::ATTR_NORMAL . self::SEPARATOR . self::FG_GREEN . self::SUFFIX;
        $this->debugColor = self::PREFIX . self::ATTR_NORMAL . self::SEPARATOR . self::FG_BLUE . self::SUFFIX;
    }

    /**
     * @see   DefaultLogger#printMessage
     *
     * @param string $message
     * @param int    $priority
     */
    protected function printMessage($message, OutputStream $stream, $priority)
    {
        if (null !== $message) {
            if (!$this->colorsSet) {
                $this->setColors();
                $this->colorsSet = true;
            }

            switch ($priority) {
                case Project::MSG_ERR:
                    $message = $this->errColor . $message . self::END_COLOR;

                    break;

                case Project::MSG_WARN:
                    $message = $this->warnColor . $message . self::END_COLOR;

                    break;

                case Project::MSG_INFO:
                    $message = $this->infoColor . $message . self::END_COLOR;

                    break;

                case Project::MSG_VERBOSE:
                    $message = $this->verboseColor . $message . self::END_COLOR;

                    break;

                case Project::MSG_DEBUG:
                    $message = $this->debugColor . $message . self::END_COLOR;

                    break;
            }

            $stream->write($message . PHP_EOL);
        }
    }

    /**
     * Set the colors to use from a property file specified by the
     * special ant property ant.logger.defaults.
     */
    private function setColors()
    {
        $userColorFile = Phing::getProperty('phing.logger.defaults');
        $systemColorFile = new File(Phing::getResourcePath('etc/default.listeners.properties'));

        $in = null;

        try {
            $prop = new Properties();

            if (null !== $userColorFile) {
                $prop->load($userColorFile);
            } else {
                $prop->load($systemColorFile);
            }

            $err = $prop->getProperty('AnsiColorLogger.ERROR_COLOR');
            $warn = $prop->getProperty('AnsiColorLogger.WARNING_COLOR');
            $info = $prop->getProperty('AnsiColorLogger.INFO_COLOR');
            $verbose = $prop->getProperty('AnsiColorLogger.VERBOSE_COLOR');
            $debug = $prop->getProperty('AnsiColorLogger.DEBUG_COLOR');
            if (null !== $err) {
                $this->errColor = self::PREFIX . $err . self::SUFFIX;
            }
            if (null !== $warn) {
                $this->warnColor = self::PREFIX . $warn . self::SUFFIX;
            }
            if (null !== $info) {
                $this->infoColor = self::PREFIX . $info . self::SUFFIX;
            }
            if (null !== $verbose) {
                $this->verboseColor = self::PREFIX . $verbose . self::SUFFIX;
            }
            if (null !== $debug) {
                $this->debugColor = self::PREFIX . $debug . self::SUFFIX;
            }
        } catch (IOException $ioe) {
            //Ignore exception - we will use the defaults.
        }
    }
}
