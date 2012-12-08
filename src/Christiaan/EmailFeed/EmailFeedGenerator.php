<?php
namespace Christiaan\EmailFeed;

use Fetch\Server;
use Suin\RSSWriter\Channel;
use Fetch\Message;
use Suin\RSSWriter\Item;
use Suin\RSSWriter\Feed;

class EmailFeedGenerator
{
    private $mailServer;
    private $feed;
    private $channel;

    public function __construct(Server $mailServer, Feed $feed, Channel $channel)
    {
        $this->mailServer = $mailServer;
        $this->feed = $feed;
        $this->channel = $channel;
    }

    public function createFeed(
        $feedFile,
        $dir,
        $url,
        $limit
    ) {
        $since = date('j F Y', strtotime($limit));

        /* @var $message Message */
        foreach ($this->mailServer->search('ALL SINCE "' . $since . '"') as $message) {
            $item = new Item();
            $item->title(utf8_encode($message->getSubject()))
                ->description(utf8_encode($message->getMessageBody()))
                ->url($url . $message->getUid() . '.html')
                ->pubDate($message->getDate())
                ->guid($message->getUid())
                ->appendTo($this->channel);

            file_put_contents($dir . '/' . $message->getUid() . '.html', $message->getMessageBody(true));
        }

        file_put_contents($feedFile, (string) $this->feed->render());
    }
}
