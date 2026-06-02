<?php

namespace App\Formatter;

use App\User\Models\User;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use s9e\SweetDOM\Element;
use s9e\TextFormatter\Configurator;
use s9e\TextFormatter\Renderer;
use s9e\TextFormatter\Unparser;
use s9e\TextFormatter\Utils;

class Formatter
{
    protected array $configurationCallbacks = [];

    protected array $parsingCallbacks = [];

    protected array $unparsingCallbacks = [];

    protected array $renderingCallbacks = [];

    public function __construct(protected Repository $cache, protected string $cacheDir) {}

    public function addConfigurationCallback($callback): void
    {
        $this->configurationCallbacks[] = $callback;
    }

    public function addParsingCallback($callback): void
    {
        $this->parsingCallbacks[] = $callback;
    }

    public function addUnparsingCallback($callback): void
    {
        $this->unparsingCallbacks[] = $callback;
    }

    public function addRenderingCallback($callback): void
    {
        $this->renderingCallbacks[] = $callback;
    }

    public function parse($text, $context = null, ?User $user = null)
    {
        $parser = $this->getParser($context);

        /*
         * Can be injected in tag or attribute filters by calling:
         * ->addParameterByName('actor') on the filter.
         * See the mentions extension's ConfigureMentions.php for an example.
         */
        $parser->registeredVars['actor'] = $user;

        foreach ($this->parsingCallbacks as $callback) {
            $text = $callback($parser, $context, $text, $user);
        }

        return $parser->parse($text);
    }

    public function render($xml, $context = null, ?Request $request = null)
    {
        $renderer = $this->getRenderer();

        foreach ($this->renderingCallbacks as $callback) {
            $xml = $callback($renderer, $context, $xml, $request);
        }

        $xml = $this->configureDefaultsOnLinks($renderer, $xml, $context, $request);

        return $renderer->render($xml);
    }

    public function unparse($xml, $context = null): ?string
    {
        foreach ($this->unparsingCallbacks as $callback) {
            $xml = $callback($context, $xml);
        }

        return $xml !== null ? Unparser::unparse($xml) : null;
    }

    public function flush(): void
    {
        $this->cache->forget('forum.formatter');
    }

    protected function getConfigurator(): Configurator
    {
        $configurator = new Configurator;

        $configurator->rootRules->enableAutoLineBreaks();

        $configurator->rendering->setEngine('PHP');
        $configurator->rendering->getEngine()->cacheDir = $this->cacheDir; // @phpstan-ignore-line

        $configurator->enableJavaScript();
        $configurator->javascript->exports = ['preview'];

        $configurator->javascript->setMinifier('MatthiasMullieMinify')
            ->keepGoing = true;

        $configurator->HTMLElements->allowElement('h1');
        $configurator->HTMLElements->allowElement('h2');
        $configurator->HTMLElements->allowElement('h3');
        $configurator->HTMLElements->allowElement('p');
        $configurator->HTMLElements->allowUnsafeAttribute('p', 'style');
        $configurator->HTMLElements->allowElement('strong');
        $configurator->HTMLElements->allowElement('em');
        $configurator->HTMLElements->allowElement('s');
        $configurator->HTMLElements->allowElement('ul');
        $configurator->HTMLElements->allowElement('li');
        $configurator->HTMLElements->allowElement('ol');
        $configurator->HTMLElements->allowElement('blockquote');
        $configurator->HTMLElements->allowElement('a');
        $configurator->HTMLElements->allowAttribute('a', 'href');
        $configurator->HTMLElements->allowAttribute('a', 'rel');
        $configurator->HTMLElements->allowUnsafeAttribute('a', 'target');

        $configurator->Escaper; /** @phpstan-ignore-line */
        $configurator->Autoemail; /** @phpstan-ignore-line */
        $configurator->Autolink; /** @phpstan-ignore-line */
        $configurator->tags->onDuplicate('replace');

        foreach ($this->configurationCallbacks as $callback) {
            $callback($configurator);
        }

        $this->configureExternalLinks($configurator);

        return $configurator;
    }

    protected function configureExternalLinks(Configurator $configurator): void
    {
        /**
         * @var Configurator\Items\TemplateDocument $dom
         */
        $dom = $configurator->tags['URL']->template->asDOM();

        foreach ($dom->getElementsByTagName('a') as $a) {
            /** @var Element $a */
            $a->prependXslCopyOf('@target');
            $a->prependXslCopyOf('@rel');
        }

        $dom->saveChanges();
    }

    protected function getComponent($name)
    {
        $formatter = $this->cache->rememberForever('forum.formatter', function () {
            return $this->getConfigurator()->finalize();
        });

        return $formatter[$name];
    }

    protected function getParser($context = null)
    {
        $parser = $this->getComponent('parser');

        $parser->registeredVars['context'] = $context;

        return $parser;
    }

    protected function getRenderer()
    {
        spl_autoload_register(function ($class) {
            if (file_exists($file = $this->cacheDir.'/'.$class.'.php')) {
                include $file;
            }
        });

        return $this->getComponent('renderer');
    }

    public function getJs()
    {
        return $this->getComponent('js');
    }

    protected function configureDefaultsOnLinks(Renderer $renderer, string $xml, $context = null, ?Request $request = null): string
    {
        return Utils::replaceAttributes($xml, 'URL', function ($attributes) {
            $attributes['rel'] = $attributes['rel'] ?? 'ugc nofollow';

            return $attributes;
        });
    }
}
