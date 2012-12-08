<?php
namespace Christiaan\EmailFeed;

use Cilex\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Fetch\Server;
use Fetch\Message;
use Suin\RSSWriter\Feed;
use Suin\RSSWriter\Channel;
use Symfony\Component\Process\Exception\RuntimeException;

class GenerateFeedCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('emailfeed:generate')
            ->setDescription('Generate the feed for the configured email account')
            ->addOption('hostname', null, InputOption::VALUE_REQUIRED, 'Hostname to connect to')
            ->addOption('port', 'P', InputOption::VALUE_OPTIONAL, 'Port of mailserver', 143)
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Type of mailserver either pop3, imap or nntp', 'imap')
            ->addOption('username', 'u', InputOption::VALUE_REQUIRED)
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED)
            ->addOption('mailbox', 'm', InputOption::VALUE_OPTIONAL, 'Mailbox to fetch mail from', 'INBOX')
            ->addOption('dir', 'd', InputOption::VALUE_REQUIRED, 'Output directory')
            ->addOption('filename', 'f', InputOption::VALUE_OPTIONAL, 'Filename of rss feed', 'feed.xml')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limit (in days) the rss feed', 7)
            ->addOption('title', null, InputOption::VALUE_REQUIRED, 'Title of the rss feed')
            ->addOption('description', 'desc', InputOption::VALUE_REQUIRED, 'Description of rss feed')
            ->addOption('url', null, InputOption::VALUE_REQUIRED, 'Url on which the feed will be published (without feed filename)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = $input->getOption('dir');
        $limit = '- '.$input->getOption('limit').' day';
        if (!is_dir($dir))
            throw new RuntimeException('Dir does not exist');

        $url = rtrim($input->getOption('url'), '/') . '/';
        $filename = $input->getOption('filename');
        $feedFile = $dir . '/' . $filename;

        $server = new Server($input->getOption('hostname'), $input->getOption('port'), $input->getOption('type'));
        $server->setAuthentication($input->getOption('username'), $input->getOption('password'));

        $feed = new Feed();
        $channel = new Channel();
        $channel->title($input->getOption('title'))
            ->description($input->getOption('description'))
            ->url($url . $filename)
            ->appendTo($feed);

        $generator = new EmailFeedGenerator($server, $feed, $channel);
        $generator->createFeed($dir, $filename, $url, $limit);

        $output->writeln('Succesfully generated ' . $feedFile);
    }
}
