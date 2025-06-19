<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateEntity extends Command
{
    protected $signature = 'generate:entity {entity}';

    protected $description = 'Generate model, repository interface, and class for specified entity';

    public function handle(): void
    {
        $entity = $this->argument('entity');
        // Create directory for models if not exists
        $modelPath = app_path('Models');
        if (!File::isDirectory($modelPath)) {
            File::makeDirectory($modelPath, 0755, true);
        }

        // Create model file if not exists
        $modelPath = "{$modelPath}/{$entity}.php";
        if (!File::exists($modelPath)) {
            $modelContent = "<?php\n\nnamespace App\Models;\n\nuse Illuminate\Database\Eloquent\Model;\n\nclass {$entity} extends Model\n{\n   //Define model properties and relationships here\n}\n";
            File::put($modelPath, $modelContent);
        }

        // Create directory for repository interfaces if not exists
        $interfacePath = app_path('Contracts/Repositories');
        if (!File::isDirectory($interfacePath)) {
            File::makeDirectory($interfacePath, 0755, true);
        }

        // Create repository interface file if not exists
        $interfacePath = "{$interfacePath}/{$entity}RepositoryInterface.php";
        if (!File::exists($interfacePath)) {
            $interfaceContent = "<?php\n\nnamespace App\Contracts\Repositories;\n\ninterface {$entity}RepositoryInterface extends RepositoryInterface\n{\n    // Define interface methods here\n}\n";
            File::put($interfacePath, $interfaceContent);
        }

        // Create directory for repository classes if not exists
        $repositoryPath = app_path('Repositories');
        if (!File::isDirectory($repositoryPath)) {
            File::makeDirectory($repositoryPath, 0755, true);
        }

        // Create repository class file if not exists
        $repositoryPath = "{$repositoryPath}/{$entity}Repository.php";
        if (!File::exists($repositoryPath)) {
            $repositoryContent = "<?php\n\n";
            $repositoryContent .= "namespace App\Repositories;\n\n";
            $repositoryContent .= "use App\Contracts\Repositories\\{$entity}RepositoryInterface;\n";
            $repositoryContent .= "use App\Models\\{$entity};\n\n";
            $repositoryContent .= "use Illuminate\Database\Eloquent\Model;\n";
            $repositoryContent .= "use Illuminate\Database\Eloquent\Collection;\n";
            $repositoryContent .= "use Illuminate\Pagination\LengthAwarePaginator;\n\n";
            $repositoryContent .= "class {$entity}Repository implements {$entity}RepositoryInterface\n";
            $repositoryContent .= "{\n";
            $repositoryContent .= "    public function __construct({$entity} \$model)\n";
            $repositoryContent .= "    {\n";
            $repositoryContent .= "        parent::__construct(\$model);\n";
            $repositoryContent .= "    }\n\n";
            $repositoryContent .= "    public function add(array \$data): string|object\n";
            $repositoryContent .= "    {\n";
            $repositoryContent .= "        // TODO: Implement add() method.\n";
            $repositoryContent .= "    }\n\n";
            $repositoryContent .= "    public function getFirstWhere(array \$params, array \$relations = []): ?Model\n";
            $repositoryContent .= "    {\n";
            $repositoryContent .= "        // TODO: Implement getFirstWhere() method.\n";
            $repositoryContent .= "    }\n\n";
            $repositoryContent .= "    public function getList(array \$orderBy = [], array \$relations = [], int|string \$dataLimit = DEFAULT_DATA_LIMIT, int \$offset = null): Collection|LengthAwarePaginator\n";
            $repositoryContent .= "    {\n";
            $repositoryContent .= "        // TODO: Implement getList() method.\n";
            $repositoryContent .= "    }\n\n";
            $repositoryContent .= "    public function getListWhere(array \$orderBy = [], string \$searchValue = null, array \$filters = [], array \$relations = [], int|string \$dataLimit = DEFAULT_DATA_LIMIT, int \$offset = null): Collection|LengthAwarePaginator\n";
            $repositoryContent .= "    {\n";
            $repositoryContent .= "        // TODO: Implement getListWhere() method.\n";
            $repositoryContent .= "    }\n\n";
            $repositoryContent .= "    public function update(string \$id, array \$data): bool\n";
            $repositoryContent .= "    {\n";
            $repositoryContent .= "        // TODO: Implement update() method.\n";
            $repositoryContent .= "    }\n\n";
            $repositoryContent .= "    public function delete(array \$params): bool\n";
            $repositoryContent .= "    {\n";
            $repositoryContent .= "        // TODO: Implement delete() method.\n";
            $repositoryContent .= "    }\n";
            $repositoryContent .= "}\n";
            File::put($repositoryPath, $repositoryContent);
        }

        // Create migration file
        $migrationName = 'create_' . Str::plural(Str::snake($entity)) . '_table';
        $this->call('make:migration', ['name' => $migrationName]);
        $this->info("{$entity} model, repository interface, and class generated successfully.");
    }
}
