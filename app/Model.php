<?php
namespace App;

class Model
{
    protected $name;
    protected $table;
    protected $fields;
    protected $timestamps = false;
    protected $softDeletes = false;

    protected const INDENT4 = "    ";
    protected const INDENT8 = "        ";
    protected const INDENT12 = "            ";

    public function __construct($data)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function generate()
    {
        exec("php artisan make:migration create_{$this->table}_table");
        exec("php artisan make:model {$this->name} -cr");
        exec("php artisan make:seed " . ucfirst($this->table) . "TableSeeder");
        exec("php artisan make:test {$this->name}Test");
        $this->addRoutes();
        $this->addUpMigration();
        $this->addDownMigration();
        $this->makeFactory();
    }

    protected function addRoutes()
    {
        $file = "routes/web.php";
        $content = file_get_contents($file);

        $lines = [
            "\n" . PHP_EOL,
            "// The routes for the {$this->name} resource" . PHP_EOL,
            "Route::group(['prefix' => '/{$this->table}'], function() {" . PHP_EOL,
            self::INDENT4 . "Route::get('/', '{$this->name}Controller@index');" . PHP_EOL,
            self::INDENT4 . "Route::get('/{\$id}', '{$this->name}Controller@show');" . PHP_EOL,
            self::INDENT4 . "Route::post('/', '{$this->name}Controller@store');" . PHP_EOL,
            self::INDENT4 . "Route::post('/{\$id}', '{$this->name}Controller@update');" . PHP_EOL,
            self::INDENT4 . "Route::delete('/{\$id}', '{$this->name}Controller@delete');" . PHP_EOL,
            "});" . PHP_EOL
        ];

        foreach ($lines as $line) {
            $content .= $line;
        }
        
        file_put_contents($file, $content);
    }

    protected function getMigrationFile()
    {
        return glob("database/migrations/*_create_{$this->table}_table.php")[0];  
    }

    protected function addUpMigration()
    {
        $new =  "up()" . PHP_EOL 
            . self::INDENT4 . "{" . PHP_EOL
            . self::INDENT8 . "Schema::create('{$this->table}'," 
            . " function (Blueprint \$table) {" . PHP_EOL
            . self::INDENT12 . "\$table->increments('id');" . PHP_EOL;

        foreach ($this->fields as $field) {
            $new .= self::INDENT12 . "";
            $new .= "\$table->{$field->type}('{$field->name}')";

            if (isset($field->nullable) && $field->nullable == true) {
                $new .= "->nullable()";
            }

            $new .= ";" . PHP_EOL;
        }

        if ($this->timestamps) {
            $new .= self::INDENT12 . "\$table->timestamps();" . PHP_EOL;
        }

        if ($this->softDeletes) {
            $new .= self::INDENT12 . "\$table->softDeletes();" . PHP_EOL;
        }

        $new .= self::INDENT8 . "});";

        $this->replaceInMigrationFile('up', $new);
    }

    protected function addDownMigration()
    {
        $new =  "down()" . PHP_EOL 
            . self::INDENT4 . "{" . PHP_EOL
            . self::INDENT8 . "Schema::dropIfExists('{$this->table}');";

        $this->replaceInMigrationFile('down', $new);
    }

    protected function replaceInMigrationFile($method, $new)
    {
        $old =  "$method()" . PHP_EOL 
            . self::INDENT4 . "{" . PHP_EOL 
            . self::INDENT8 . "//";

        $this->findAndReplaceInFile($this->getMigrationFile(), $old, $new);
    }

    protected function findAndReplaceInFile($file, $old, $new)
    {
        $contents = file_get_contents($file);
        $contents = str_replace($old, $new, $contents);

        return file_put_contents($file, $contents);
    }

    protected function makeFactory()
    {
        $contents = "<?php" . PHP_EOL
            . PHP_EOL
            . "\$factory->define(App\\" . $this->name . "::class,"
            . " function (Faker\Generator \$faker) {" . PHP_EOL
            . self::INDENT4 . "return [" . PHP_EOL;
        
        foreach ($this->fields as $field) {
            $contents .= self::INDENT8 . '"' . $field->name . '" => '
                . $this->getFactoryValue($field) 
                . "," . PHP_EOL;
        }

        $contents .= self::INDENT4 . "];" . PHP_EOL
            . "});";

        $file = "database/factories/" . $this->name . "Factory.php";
        
        return file_put_contents($file, $contents);
    }

    protected function getFactoryValue($field)
    {
        if ($field->type == 'integer') {
            return "rand(1,5000)";
        } else {
            return "\$faker->name";
        }
    }
}