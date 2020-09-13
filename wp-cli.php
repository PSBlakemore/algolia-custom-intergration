<?php

if (!(defined('WP_CLI') && WP_CLI)) {
    return;
}

$indice = $_ENV['INDICE'];

class Algolia_Command {

    public function reindex_post($args, $assoc_args) {
        global $algolia;
        $index = $algolia->initIndex($indice);
    
        $index->clearObjects()->wait();
    
        $paged = 1;
        $count = 0;
    
        do {
            $posts = new WP_Query([
                'posts_per_page' => 100,
                'paged' => $paged,
                'post_type' => array( 'post', 'page', 'projects', 'team_member' )
            ]);
    
            if (!$posts->have_posts()) {
                break;
            }
    
            $records = [];
    
            foreach ($posts->posts as $post) {
                if ($assoc_args['verbose']) {
                    WP_CLI::line('Serializing ['.$post->post_title.']');
                }
                $record = (array) apply_filters('post_to_record', $post);
    
                if (!isset($record['objectID'])) {
                    $record['objectID'] = implode('#', [$post->post_type, $post->ID]);
                }
    
                $records[] = $record;
                $count++;
            }
    
            if ($assoc_args['verbose']) {
                WP_CLI::line('Sending batch');
            }
    
            $index->saveObjects($records);
    
            $paged++;
    
        } while (true);
    
        WP_CLI::success("$count posts indexed in Algolia");
    }

    public function copy_config($args, $assoc_args) {
        global $algolia;
    
        $srcIndexName = $assoc_args['from'];
        $destIndexName = $assoc_args['to'];
    
        if (!$srcIndexName || !$destIndexName) {
            throw new InvalidArgumentException('--from and --to arguments are required');
        }
    
        $scope = [];
    
        if (isset($assoc_args['settings']) && $assoc_args['settings']) {
            $scope[] = 'settings';
        }
        if (isset($assoc_args['synonyms']) && $assoc_args['synonyms']) {
            $scope[] = 'synonyms';
        }
        if (isset($assoc_args['rules']) && $assoc_args['rules']) {
            $scope[] = 'rules';
        }
    
        if (!empty($scope)) {
            $algolia->copyIndex($srcIndexName, $destIndexName, ['scope' => $scope]);
            WP_CLI::success('Copied '.implode(', ', $scope)." from $srcIndexName to $destIndexName");
        } else {
            WP_CLI::warning('Nothing to copy, use --settings, --synonyms or --rules.');
        }
    }
    
    
}

WP_CLI::add_command('algolia', 'Algolia_Command');
