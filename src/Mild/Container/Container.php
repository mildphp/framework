<?php

namespace Mild\Container;

use Closure;
use Throwable;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionException;
use ReflectionFunctionAbstract;
use Mild\Contract\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @var array
     */
    protected $bindings = [];
    /**
     * @var array
     */
    private $aliasBindings = [];
    /**
     * @var array
     */
    private $bindingAliases = [];
    /**
     * @var array
     */
    private $resolvedBindings = [];

    /**
     * @param string $key
     * @return mixed
     * @throws ReflectionException
     * @throws Throwable
     */
    public function get($key)
    {
        if (!$this->has($key = $this->resolveKey($key))) {
            throw new NotFoundException(sprintf(
                'Binding [%s] does not exists.', $key
            ));
        }

        // Jika binding sudah di selesaikan, maka kita akan memanggil hasil penyelesaian yang
        // di simpan di dalam resolvedBindings properti. ini berguna ketika anda mendefinisikan sebuah
        // binding yang bernilaikan callable dan callable itu ketika di panggil adalah tipe data string.
        // Ataupun ketika anda mendefinisikan sebuah string nama objek, kita akan memanggil objek tersebut
        // dan kemudian objek tersebut di simpan di dalam penyelesaian binding.
        if (isset($this->resolvedBindings[$key])) {
            return $this->resolvedBindings[$key];
        }

        return $this->resolveBinding($key, $this->bindings[$key]);
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->bindings;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->bindings[$this->resolveKey($key)]);
    }

    /**
     * @param $key
     * @return void
     */
    public function put($key)
    {
        unset($this->bindings[$key = $this->resolveKey($key)], $this->resolvedBindings[$key]);

        // Jika anda telah mengaliaskan binding tersebut, maka kita akan menghapus semua
        // alias dari binding tersebut sehingga alias dari binding tersebut tidak bisa
        // di panggil kembali.
        if (isset($this->bindingAliases[$key])) {
            foreach ($this->bindingAliases[$key] as $alias) {
                unset($this->aliasBindings[$alias]);
            }
            unset($this->bindingAliases[$key]);
        }
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function set($key, $value)
    {
        $this->bindings[$key] = $value;
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function bind($key, $value)
    {
        $this->set($key, $value);

        // Jika binding yang anda registrasikan adalah binding tidak perlu di selesaikan, maka kita
        // akan menambahkan binding tersebut kedalam binding yang sudah di selesaikan.
        if (is_callable($value)) {
            $value = call_user_func($value);
        }

        $this->resolvedBindings[$key] = $value;
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function alias($key, $value)
    {
        // Jika anda sebelumnya sudah menambahkan alias dengan key dari value
        // maka kita akan menghindari duplikat alias yang sudah di daftarkan.
        if (!isset($this->aliasBindings[$key])) {
            $this->aliasBindings[$key] = $value;

            // Kita akan menambahkan sekumpulan alias dari kata kunci binding yang sudah
            // di tambahkan, ini berguna jika anda menghapus binding dari metode put()
            // semua alias yang telah anda tambahkan maka akan di hapus juga.
            $this->bindingAliases[$value][] = $key;
        }
    }

    /**
     * @param $abstract
     * @param array $arguments
     * @return mixed
     * @throws ReflectionException
     * @throws Throwable
     */
    public function make($abstract, array $arguments = [])
    {
        if (is_string($abstract)) {

            // Jika anda sebelumnya telah mendefinisikan binding dengan kunci yang sama
            // dengan nilai string variabel callable, maka kita akan memanggil binding tersebut
            if ($this->has($abstract = $this->resolveKey($abstract))) {
                return $this->get($abstract);
            }

            // Jika callable yang anda berikan adalah string, dan string dari callable
            // yang anda berikan teridentifikasi titik [.] maka kita mengira anda akan
            // memanggil semuah metode di dalam sebuah objek, tentunya kita akan kembali memanggil
            // metode call() dengan callable yang baru.
            if (strpos($abstract, '.') !== false) {
                return $this->make(explode('.', $abstract, 2), $arguments);
            }

            // Kita tidak tahu bahwa anda memanggil sebuah objek ataupun sebuah fungsi, maka kita
            // akan mencoba untuk memanggil ReflectionClass terlebih dahulu, ketika objek tidak
            // di temukan / menghasilkan error, maka kita akan mencoba memanggil sebuah fungsi.
            try {
                $reflection = new ReflectionClass($abstract);
            } catch (Throwable $e) {
                $reflection = new ReflectionFunction($abstract);
            }
        } elseif (is_callable($abstract)) {

            // Jika abstract callable yang diberikan adalah object tetapi object itu bukan Closure,
            // maka kita akan reset kembali dengan menambahkan __invoke method di dalam abstract.
            // atau kita bisa sebut juga callable object.
            if (is_object($abstract) && $abstract instanceof Closure === false) {
                return $this->make([$abstract, '__invoke'], $arguments);
            }

            if (!is_array($abstract)) {

                // Jika callback adalah callable, dan itu bukan array, maka kita asumsikan bahwa
                // callable yang anda maksud adalah adalah fungsi ataupun objek Closure.
                $reflection = new ReflectionFunction($abstract);
            } else {

                $reflection = new ReflectionMethod($abstract[0], $abstract[1]);

                if (false === $reflection->isStatic() && is_string($abstract[0])) {
                    $abstract[0] = $this->make($abstract[0]);
                }
            }
        } else {
            throw new InvalidAbstractException($abstract);
        }

        if ($reflection instanceof ReflectionClass === false) {
            return $abstract(...$this->parseArguments($reflection, $arguments));
        }

        // Ketika variable reflection itu adalah instance dari ReflectionClass,
        // kita akan memanggil objek tersebut, tetapi kita juga harus mengetahui
        // bahwa objek tersebut mempunyai metode constructor atau tidak, ketika
        // objek tersebut mempunyai constructor, maka kita akan menginject parameter
        // dari metode construtor objek tersebut.
        if (($constructor = $reflection->getConstructor()) === null) {
            return new $abstract;
        }

        return new $abstract(...$this->parseArguments($constructor, $arguments));
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }

    /**
     * @param $name
     * @return mixed
     * @throws ReflectionException
     * @throws Throwable
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param $name
     * @return void
     */
    public function __unset($name)
    {
        $this->put($name);
    }

    /**
     * @param $name
     * @param $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed
     * @throws ReflectionException
     * @throws Throwable
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->put($offset);
    }

    /**
     * Metode ini berfungsi untuk menyelesaikan key apabila key itu adalah alias dari binding.
     * Jika alias itu tidak di temukan maka key asli dikembalikan.
     * Tetapi jika key itu adalah bukan binding / masih alias dari binding, maka kita akan
     * Terus mencari key dari binding yang di aliaskan.
     *
     * @param $key
     * @return string
     */
    protected function resolveKey($key)
    {
        if (!isset($this->aliasBindings[$key]) || $key === $this->aliasBindings[$key]) {
            return $key;
        }

        return $this->resolveKey($this->aliasBindings[$key]);
    }

    /**
     * @param $key
     * @param $binding
     * @return mixed
     * @throws ReflectionException
     * @throws Throwable
     */
    protected function resolveBinding($key, $binding)
    {
        // Jika tipe data dari binding tersebut instansi dari Closure / anonymous function,
        // maka kita akan menyelesaikan binding yang anda definisikan kemudian binding tersebut
        // akan kita simpan di dalam resolvedBindings properti.
        if ($binding instanceof Closure) {
            return $this->resolvedBindings[$key] = $binding($this);
        }

        // Jika tipe data dari binding tersebut adalah string, maka kita akan menyelesaikan
        // binding yang anda definisikan kemudian binding tersebut akan kita simpan di dalam
        // resolvedBindings properti.
        if (is_string($binding)) {

            // Jika key dari binding sama dengan binding, untuk menghindari overload
            // yang terus mencari binding, maka dari itu kita akan menghapus sementara
            // binding yang anda definisikan, setelah binding di selesaikan maka kita akan
            // mendaftarkan kembali binding yang anda definisikan
            if ($key === $binding) {
                unset($this->bindings[$key]);
                $this->resolvedBindings[$key] = $this->make($binding);
                $this->bindings[$key] = $binding;
                return $this->resolvedBindings[$key];
            }

            // Jika anda mendefinisikan alias dengan key yang sama dengan value
            // dari binding dan binding yang di aliaskan itu belum di selesaikan,
            // maka kita akan menghapus sementara alias tersebut untuk menghindari
            // overload yang terus mencari key alias dari binding, binding di selesaikan
            // maka kita akan mengaliaskan kembali binding yang anda definisikan.
            if (isset($this->aliasBindings[$binding]) && $this->aliasBindings[$binding] === $key) {
                unset($this->aliasBindings[$binding]);
                $this->resolvedBindings[$key] = $this->make($binding);
                $this->aliasBindings[$binding] = $key;
                return $this->resolvedBindings[$key];
            }
        }

        return $this->resolvedBindings[$key] = $this->make($binding);
    }

    /**
     * Metode ini berguna untuk menginject argument dari parameter di dalam fungsi,
     * Metode atau objek. anda bisa menambahkan parameter di dalam arguments parameter
     * Dibawah ini ketika parameter di dalam sebuah objek atau fungsi atau metode itu tidak
     * Didefinisikan didalam container.
     *
     * @param ReflectionFunctionAbstract $reflector
     * @param array $arguments
     * @return array
     * @throws ReflectionException
     * @throws Throwable
     */
    protected function parseArguments($reflector, $arguments = [])
    {
        [$index, $parameters] = [0, []];

        foreach ($reflector->getParameters() as $parameter) {
            $position = $parameter->getPosition();

            // Jika anda mendefinisikan dengan key yang sama dengan nama dari parameter
            // kami akan mendeteksi bahwa anda mendefinisikan parameter sesuai indeks
            // nama dari parameter tersebut
            if(isset($arguments[$parameter->name])) {
                $parameters[$position] = $arguments[$parameter->name];
            } elseif (($class = $parameter->getClass())) {

                // Ketika parameter yang di definisikan adalah sebuah objek, Kita akan mencoba menginject
                // dari arguments yang anda berikan ataupun dari binding yang anda sudah definisikan
                // ataupun memanggil kelas tersebut.
                if (isset($arguments[$index]) && $arguments[$index] instanceof $class->name) {

                    // Ketika parameter yang di definisikan adalah sebuah objek, dan anda menambahkan
                    // objek tersebut di dalam arguments, maka akan memanggil arguments tersebut
                    $parameters[$position] = $arguments[$index];
                } else {

                    // Ketika anda memanggil sebuah kelas yang tidak di definisikan di dalam arguments,
                    // ataupun di dalam container, kita tidak memakai arguments untuk menginject kelas
                    // yang di panggil.
                    try {
                        $parameters[$position] = $this->make($class->name);
                    } catch (Throwable $e) {

                        // Ketika parameter yang di definisikan tidak optional, maka kita akan lemparkan
                        // error tersebut, semisalnya anda mendefinisikan interface yang tidak di daftar
                        // kan di container tetapi anda mendefinisikan di dalam parameter sehingga kita
                        // tidak tahu apa yang anda inginkan untuk parameter tersebut.
                        if (!$parameter->isOptional()) {
                            throw $e;
                        }

                        $parameters[$position] = null;
                    }

                    // Ketika Container meng-generate value parameter yang akan di inject, maka kita,
                    // tidak akan menambahkan index.
                    continue;
                }
            } elseif (array_key_exists($index, $arguments)) {

                // Ketika parameter yang di definisikan itu bukan objek dan bukan default value atau
                // nilai yang harus di isi dan anda menambahkan parameter tersebut di dalam arguments
                // sesuai dengan positi yang ada di dalam paramter, maka kita akan memakai argument
                // tersebut.
                $parameters[$position] = $arguments[$index];
            } elseif ($parameter->isDefaultValueAvailable()) {

                // Ketika parameter yang di definisikan adalah nilai default, tentunya kita akan
                // memanggil nilai default yang di definisikan.
                $parameters[$position] = $parameter->getDefaultValue();
            }

            ++$index;
        }

        // Ketika anda tidak mendefinisikan parameters di dalam fungsi atau method yang ingin anda
        // panggil, maka kita akan mengembalikan arguments yang anda berikan, ini berguna ketika di
        // dalam method tersebut anda memanggil fungsi func_get_args()
        return $parameters ?: $arguments;
    }
}