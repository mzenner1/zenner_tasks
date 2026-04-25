<?php

namespace App\Support;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\DefaultAttributes\DefaultAttributesExtension;
use League\CommonMark\MarkdownConverter as LeagueMarkdownConverter;

class MarkdownConverter
{
    private static ?LeagueMarkdownConverter $converter = null;

    public static function toHtml(string $markdown): string
    {
        if (self::$converter === null) {
            $environment = new Environment([
                'html_input'         => 'strip',
                'allow_unsafe_links' => false,
                'renderer'           => ['soft_break' => "<br />\n"],
                'default_attributes' => [
                    \League\CommonMark\Extension\CommonMark\Node\Inline\Link::class => [
                        'target' => '_blank',
                        'rel'    => 'noopener noreferrer',
                    ],
                ],
            ]);

            $environment->addExtension(new CommonMarkCoreExtension());
            $environment->addExtension(new AutolinkExtension());
            $environment->addExtension(new DefaultAttributesExtension());

            self::$converter = new LeagueMarkdownConverter($environment);
        }

        return self::$converter->convert($markdown)->getContent();
    }
}
