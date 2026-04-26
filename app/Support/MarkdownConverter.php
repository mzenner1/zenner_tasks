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

        // Pre-process: swap @[Name](user:ID) for a private-use token BEFORE
        // CommonMark sees it. This stops the parser from rendering it as an
        // <a href="user:ID"> link entirely.
        $markdown = preg_replace_callback(
            '/@\[([^\]]+)\]\(user:([^)]+)\)/',
            fn ($m) => "\x02mention:{$m[2]}:{$m[1]}\x03",
            $markdown
        );

        $html = self::$converter->convert($markdown)->getContent();

        // Post-process: replace each token with a styled, non-interactive chip.
        $html = preg_replace_callback(
            '/\x02mention:([^:]+):([^\x03]+)\x03/',
            fn ($m) => '<span class="mention" data-user-id="' . htmlspecialchars($m[1], ENT_QUOTES) . '">@' . htmlspecialchars($m[2], ENT_QUOTES) . '</span>',
            $html
        );

        return $html;
    }
}
