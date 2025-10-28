<?php

namespace owldesign\qarr\rules;

use Craft;
use craft\helpers\ArrayHelper;

class RuleChecker
{
    protected string $replaceWith = '****';
    protected array $words = [];
    protected array $filterChecks = [];
    protected bool $replaceFullWords = true;
    protected bool $multiCharReplace = false;
    protected array $strReplace = [
        'a' => '(a|a\.|a\-|4|@|Á|á|À|Â|à|Â|â|Ä|ä|Ã|ã|Å|å|α|Δ|Λ|λ)',
        'b' => '(b|b\.|b\-|8|\|3|ß|Β|β)',
        'c' => '(c|c\.|c\-|Ç|ç|¢|€|<|\(|{|©)',
        'd' => '(d|d\.|d\-|&part;|\|\)|Þ|þ|Ð|ð)',
        'e' => '(e|e\.|e\-|3|€|È|è|É|é|Ê|ê|∑)',
        'f' => '(f|f\.|f\-|ƒ)',
        'g' => '(g|g\.|g\-|6|9)',
        'h' => '(h|h\.|h\-|Η)',
        'i' => '(i|i\.|i\-|!|\||\]\[|]|1|∫|Ì|Í|Î|Ï|ì|í|î|ï)',
        'j' => '(j|j\.|j\-)',
        'k' => '(k|k\.|k\-|Κ|κ)',
        'l' => '(l|1\.|l\-|!|\||\]\[|]|£|∫|Ì|Í|Î|Ï)',
        'm' => '(m|m\.|m\-)',
        'n' => '(n|n\.|n\-|η|Ν|Π)',
        'o' => '(o|o\.|o\-|0|Ο|ο|Φ|¤|°|ø)',
        'p' => '(p|p\.|p\-|ρ|Ρ|¶|þ)',
        'q' => '(q|q\.|q\-)',
        'r' => '(r|r\.|r\-|®)',
        's' => '(s|s\.|s\-|5|\$|§)',
        't' => '(t|t\.|t\-|Τ|τ)',
        'u' => '(u|u\.|u\-|υ|µ)',
        'v' => '(v|v\.|v\-|υ|ν)',
        'w' => '(w|w\.|w\-|ω|ψ|Ψ)',
        'x' => '(x|x\.|x\-|Χ|χ)',
        'y' => '(y|y\.|y\-|¥|γ|ÿ|ý|Ÿ|Ý)',
        'z' => '(z|z\.|z\-|Ζ)',
    ];
    protected $replaceWithLength;
    protected array $config = [];
    protected array $filteredStrings = [];
    protected bool $wasFiltered = false;

    public function __construct($words)
    {
        $this->reset();
        $this->words = $words;
        $this->generateFilterChecks();
    }

    public function reset(): static
    {
        $this->replaceFullWords($this->replaceFullWords);
        $this->replaceWith($this->replaceWith);
        return $this;
    }

    public function replaceWith($string): static
    {
        $this->replaceWith = $string;
        $this->replaceWithLength = mb_strlen($this->replaceWith);
        $this->multiCharReplace = $this->replaceWithLength === 1;
        return $this;
    }

    public function replaceFullWords($boolean): static
    {
        $this->replaceFullWords = $boolean;
        $this->generateFilterChecks();
        return $this;
    }

    private function resetFiltered(): void
    {
        $this->filteredStrings = [];
        $this->wasFiltered = false;
    }

    public function filter($string, $details = null)
    {
        $this->resetFiltered();
        if (!is_string($string) || !trim($string)) {
            return $string;
        }
        $filtered = $this->filterString($string);
        if ($details) {
            return [
                'orig' => $string,
                'clean' => $filtered,
                'hasMatch' => $this->wasFiltered,
                'matched' => $this->filteredStrings,
            ];
        }
        return $filtered;
    }

    public function noProfanity($string)
    {
        $this->resetFiltered();
        if (!is_string($string) || !trim($string)) {
            return;
        }
        $filtered = $this->filterString($string);
        foreach ($this->words as $word) {
            if (stripos($string, $word) !== false) {
                return false;
            }
        }
        return true;
    }

    private function filterString($string): array|string|null
    {
        return preg_replace_callback($this->filterChecks, function ($matches) {
            return $this->replaceWithFilter($matches[0]);
        }, $string);
    }

    private function setFiltered($string): void
    {
        $this->filteredStrings[] = $string;
        if (!$this->wasFiltered) {
            $this->wasFiltered = true;
        }
    }

    private function replaceWithFilter($string): string
    {
        $this->setFiltered($string);
        $strlen = mb_strlen($string);
        if ($this->multiCharReplace) {
            return str_repeat($this->replaceWith, $strlen);
        }
        return $this->randomFilterChar($strlen);
    }

    private function generateFilterChecks(): void
    {
        foreach ($this->words as $string) {
            $this->filterChecks[] = $this->getFilterRegexp($string);
        }
    }

    private function getFilterRegexp($string): string
    {
        $replaceFilter = $this->replaceFilter($string);
        if ($this->replaceFullWords) {
            return '/\b' . $replaceFilter . '\b/iu';
        }
        return '/' . $replaceFilter . '/iu';
    }

    private function replaceFilter($string): array|string
    {
        return str_ireplace(array_keys($this->strReplace), array_values($this->strReplace), $string);
    }

    private function randomFilterChar($len): string
    {
        return str_shuffle(str_repeat($this->replaceWith, intval($len / $this->replaceWithLength)) . substr($this->replaceWith, 0, ($len % $this->replaceWithLength)));
    }
}