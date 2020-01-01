<?php

namespace Mild\View;

use RuntimeException;
use Mild\Support\Traits\Macroable;
use Mild\Contract\View\CompilerInterface;

class Compiler implements CompilerInterface
{
    use Macroable;

    /**
     * @var string
     */
    protected $path;
    /**
     * @var array
     */
    private $parts = [];
    /**
     * @var bool
     */
    private $switch = false;

    /**
     * Compiler constructor.
     *
     * @param $path
     */
    public function __construct($path)
    {
        if (!is_writable($path)) {
            throw new RuntimeException(sprintf(
                'Directory %s is not writable.', $path
            ));
        }
        $this->path = rtrim($path, '\/');
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param $contents
     * @return string
     */
    public function compile($contents)
    {
        // Hilangkan semua komentar di dalam tag, misalnya dalam tag html anda
        // mendefinisikan bahwa setiap partial itu berbeda, kita tidak akan
        // menampilkan itu ke dalam file yang di compile, sehingga di browser
        // anda tidak akan melihat tag yang di komentar.
        $contents = trim(preg_replace('/{{--(.*?)--}}/s', '', $contents));

        // Mengganti tag @php @endphp menjadi kode PHP
        $contents = preg_replace_callback('/(?<!@)@php(.*?)@endphp/s', [$this, 'replacePhp'], $contents);

        // Print string dengan html, maksudnya jika di dalam string yang akan di tampilkan
        // ke layar, maka kita mengabaikan kode html tersebut sehingga kode html itu
        // bisa di pakai, Hati hati dalam menggunakan tag ini.
        // Contoh: {!! '<b>test</b>' !!} -> 'test'
        $contents = preg_replace_callback('/(@)?{!!\s*(.+?)\s*!!}?/s', [$this, 'replaceEcho'], $contents);

        // Print string mengeskekusi kode html, maksudnya jika di dalam string terdapat
        // kode html, maka kita tidak akan mengeksekusi kode html itu
        // Contoh: {{ '<b>test</b>' }} -> '<b>test</b>'
        $contents = preg_replace_callback('/(@)?{{\s*(.+?)\s*}}?/s', [$this, 'replaceEchoEscaped'], $contents);
        $contents = preg_replace_callback('/(@)?{{{\s*(.+?)\s*}}}?/s', [$this, 'replaceEchoEscaped'], $contents);

        // Tag statement ini mengganti kode yang anda masukan di dalam original file
        // dengan fungsi yang ada ataupun metode yang ada di dalam compiler.
        // Contoh: @foreach($array as $key => $value) @endforeach -> metode yang ada di dalam fungsi
        $contents = preg_replace_callback('/@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', [$this, 'replaceDefault'], $contents);

        // Jika anda menambahkan parts ke dalam view maka kita akan mengeluarkan parts
        // yang anda tambahkan di akhir konten yang sudah di kompilasi.
        if (!empty($this->parts)) {
            $contents .= "\n".implode("\n", $this->parts);
            // Jika parts sudah di masukan ke dalam view, maka kita akan kembalikan
            // parts menjadi array kosong.
            $this->parts = [];
        }

        return trim($contents);
    }

    /**
     * Mengkompile kode untuk melakukan pembokingan tempat yang di isi
     * oleh template anak.
     *
     * Contoh: @yield('name')
     *
     * @param $expression
     * @return string
     */
    protected function yieldCompile($expression)
    {
        return '<?php echo $this->getSection'.$expression.'; ?>';
    }

    /**
     * Mengkompile kode untuk melakukan pengantungan template anak kepada
     * template parent.
     *
     * Contoh: @extends('template.mld')
     *
     * @param $expression
     * @return void
     */
    protected function extendsCompile($expression)
    {
        $this->parts[] = $this->includeCompile($expression);
    }

    /**
     * Mengkompile kode untuk memasukan template lain
     *
     * Contoh: @include('template.mld')
     *
     * @param $expression
     * @return string
     */
    protected function includeCompile($expression)
    {
        return '<?php echo $__view->make('.$this->filterParentheses($expression).', get_defined_vars(), $this->sections)->render(); ?>';
    }

    /**
     * Tag pembuka untuk section
     *
     * Contoh: @section('name')
     *
     * @param $expression
     * @return string
     */
    protected function sectionCompile($expression)
    {
        return '<?php $this->startSection'.$expression.'; ?>';
    }

    /**
     * Tag penutup untuk section
     *
     * Contoh: @endsection
     *
     * @return string
     */
    protected function endSectionCompile()
    {
        return '<?php $this->endSection(); ?>';
    }

    /**
     * Tag pembuka if kondisi
     *
     * Contoh: @if($bool === true)
     *
     * @param $expression
     * @return string
     */
    protected function ifCompile($expression)
    {
        return '<?php if'.$expression.': ?>';
    }

    /**
     * @param null $expression
     * @return string
     */
    protected function phpCompile($expression = null)
    {
        if ($expression === null) {
            return '<?php';
        }
        return '<?php '.$expression.'; ?>';
    }

    /**
     * Tag elseif
     *
     * Contoh: @elseif($bool === false)
     *
     * @param $expression
     * @return string
     */
    protected function elseIfCompile($expression)
    {
        return '<?php elseif'.$expression.': ?>';
    }

    /**
     * Tag else
     *
     * Contoh: @else
     *
     * @return string
     */
    protected function elseCompile()
    {
        return '<?php else: ?>';
    }

    /**
     * Tag penutup kondisi
     *
     * Contoh: @endif
     *
     * @return string
     */
    protected function endIfCompile()
    {
        return '<?php endif; ?>';
    }

    /**
     * @param $expression
     * @return string
     */
    protected function forCompile($expression)
    {
        return '<?php for'.$expression.': ?>';
    }

    /**
     * @return string
     */
    protected function endForCompile()
    {
        return '<?php endfor; ?>';
    }

    /**
     * @param $expression
     * @return string
     */
    protected function forEachCompile($expression)
    {
        return '<?php $__iteration = 1; foreach'.$expression.': ?>';
    }

    /**
     * @return string
     */
    protected function endForEachCompile()
    {
        return '<?php ++$__iteration; endforeach; unset($__iteration); ?>';
    }

    /**
     * @param $expression
     * @return string
     */
    protected function whileCompile($expression)
    {
        return '<?php $__iteration = 1; while'.$expression.': ?>';
    }

    /**
     * @return string
     */
    protected function endWhileCompile()
    {
        return '<?php ++$__iteration; endwhile; unset($__iteration) ?>';
    }

    /**
     * @return string
     */
    protected function iterationCompile()
    {
        return '<?php if(isset($__iteration)) echo $__iteration; ?>';
    }

    /**
     * Tag kondisi continue
     *
     * Contoh: @continue
     *
     * @return string
     */
    protected function continueCompile()
    {
        return '<?php continue; ?>';
    }

    /**
     * Tag kondisi switch
     *
     * Contoh: @switch
     *
     * @param $expression
     * @return string
     */
    protected function switchCompile($expression)
    {
        $this->switch = true;
        return '<?php switch'.$expression.':';
    }

    /**
     * Tag kondisi case
     *
     * Contoh: @case('expression')
     *
     * @param $expression
     * @return string
     */
    protected function caseCompile($expression)
    {
        // Jika pada sebelumnya anda sudah memasukan tag switch maka tag case
        // tidak membutuhkan tag pembuka dari <?php
        if ($this->switch) {
            $this->switch = false;
            return 'case '.$expression.': ?>';
        }
        return '<?php case '.$expression.': ?>';
    }

    /**
     * @return string
     */
    protected function defaultCompile()
    {
        return '<?php default: ?>';
    }

    /**
     * Tag kondisi break
     *
     * Contoh: @break
     *
     * @return string
     */
    protected function breakCompile()
    {
        return '<?php break; ?>';
    }

    /**
     * @return string
     */
    protected function endSwitchCompile()
    {
        return '<?php endswitch; ?>';
    }

    /**
     * @return string
     */
    protected function csrfCompile()
    {
        return '<?php echo csrf_field(); ?>';
    }

    /**
     * @param $expression
     * @return string
     */
    protected function methodCompile($expression)
    {
        return '<?php echo method'.$expression.'; ?>';
    }

    /**
     * @param $expression
     * @return string
     */
    protected function unsetCompile($expression)
    {
        return '<?php unset'.$expression.'; ?>';
    }

    /**
     * @param $expression
     * @return string
     */
    protected function ddCompile($expression)
    {
        return '<?php dd'.$expression.'; ?>';
    }

    /**
     * Menghapus tanda kurung, misalnya di dalam kode yang akan di ganti
     * dengan kode php, anda mungkin tidak membutuhkan tanda kurung yang
     * sebelumnya di masukan oleh anda.
     *
     * Contoh: ('example') -> 'example'
     *
     * @param $expression
     * @return string
     */
    protected function filterParentheses($expression)
    {
        if (strpos($expression, '(') !== false) {
            $expression = substr($expression, 1, -1);
        }
        return $expression;
    }

    /**
     * @param $matches
     * @return string
     */
    private function replacePhp($matches)
    {
        return '<?php'.$matches[1].'?>';
    }

    /**
     * @param $matches
     * @return string
     */
    private function replaceEcho($matches)
    {
        return $matches[1] ? $matches[0] : '<?php echo '.$matches[2].'; ?>';
    }

    /**
     * @param $matches
     * @return string
     */
    private function replaceEchoEscaped($matches)
    {
        return $matches[1] ? $matches[0] : '<?php echo htmlspecialchars('.$matches[2].', ENT_QUOTES, \'UTF-8\', true) ?>';
    }

    /**
     * @param $matches
     * @return string
     */
    private function replaceDefault($matches)
    {
        $expression = null;

        if (isset($matches[3])) {
            $expression = $matches[3];
        }

        if (strpos($matches[1], '@') !== false) {
            $matches[0] = $matches[1].$expression;
        }

        if (method_exists($this, $method = $matches[1].'Compile')) {
            $matches[0] = $this->$method($expression);
        } elseif (self::hasMacro($matches[1])) {
            $matches[0] = $this->{($matches[1])}($expression);
        }

        return $matches[0];
    }
}