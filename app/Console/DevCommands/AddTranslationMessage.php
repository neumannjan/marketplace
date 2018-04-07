<?php

namespace App\Console\DevCommands;

use App\Console\Translation\TranslationFileNodeVisitor;
use Illuminate\Console\Command;
use Illuminate\Foundation\Application;
use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Command that quickly modifies or adds new translation message to all languages.
 * Should only be used in development as this command modifies existing `.php` files.
 */
class AddTranslationMessage extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'translate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Quickly add/modify a translation message.'
    . ' Should only be used in development as this command modifies existing .php files.';

    /** @var Application */
    protected $application;

    /**
     * Console command constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        parent::__construct();
        $this->application = $application;

        $locales = config('app.available_locales');

        $this->addArgument('key', InputArgument::REQUIRED);

        foreach ($locales as $locale) {
            $this->addArgument($locale, InputArgument::REQUIRED);
        }

        $this->addOption('force', 'f', InputOption::VALUE_NONE,
            'Force create the file if it does not exist.');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $keys = explode('.', $this->argument('key'));
        $firstKey = array_shift($keys);

        if (count($keys) === 0) {
            throw new \InvalidArgumentException("Key has to be composed of multiple keys separated with dots.");
        }

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $prettyPrinter = new Standard([
            'shortArraySyntax' => true
        ]);

        foreach (config('app.available_locales') as $locale) {
            $message = $this->argument($locale);

            $fileName = $this->application->resourcePath("lang/$locale/$firstKey.php");

            $traverser = new NodeTraverser();
            $traverser->addVisitor(new TranslationFileNodeVisitor($keys, $message));

            if (file_exists($fileName)) {
                $data = file_get_contents($fileName);
            } elseif ($this->option('force')) {
                $name = str_replace(['-', '_'], ' ', $firstKey);
                $cName = ucwords($name);
                $data = <<<PHP
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | $cName Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain $name
    |
    */
    
    '$keys[0]' => null
];
PHP;
            } else {
                $this->error("The file $locale/$firstKey.php does not exist.");
                continue;
            }

            try {
                $ast = $parser->parse($data);

            } catch (Error $error) {
                echo "Parse error: {$error->getMessage()}\n";
                return;
            }

            $ast = $traverser->traverse($ast);
            if (file_put_contents($fileName, $prettyPrinter->prettyPrintFile($ast)) !== false) {
                $this->info("$locale: \"$message\"");
            } else {
                $this->error("$locale: FAILURE");
            }
        }
    }
}
