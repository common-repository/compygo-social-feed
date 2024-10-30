<?php
namespace CompygoSocialFeed\Model;

use CompygoSocialFeed\Helper\Validator;
use CompygoSocialFeed\Model\Source as SourceModel;
use CompygoSocialFeed\Helper\DataHelper;
use CompygoSocialFeed\Helper\UploaderHelper;

class Feed
{
    const DEFAULT_PADDING = 20;
    const DEFAULT_BORDER_RADIUS = 5;
    const FEED_MAP_VALIDATOR = [
        'id' => 'int',
        'name' => 'string',
        'config' => [
            'type' => 'string',
            'feed_layout' => 'string',
            'post_layout' => 'string',
            'slider' => 'bool',
            'comments' => [
                'enable' => 'bool',
            ],
            'loading' => [
                'enable' => 'bool',
                'type' => 'string',
                'animation' => 'string',
            ],
            'lightbox' => ['enable' => 'bool'],
            'source' => [],
            'color' => [
                'bg' => 'color',
                'bg_2' => 'color',
                'bg_3' => 'color',
                'title' => 'color',
                'text' => 'color',
                'text_2' => 'color',
                'link' => 'color',
                'border' => 'color',
            ],
            'size' => [
                'title' => 'int',
                'date' => 'int',
                'text' => 'int',
                'action' => 'int',
            ],
            'design' => [
                'layout' => [
                    'col' => [
                        'm' => 'int',
                        't' => 'int',
                        'd' => 'int',
                    ]
                ],
                'post' => [
                    'border' => 'bool',
                    'border_radius' => 'int',
                    'image_gap' => 'bool',
                    'padding' => 'int',
                    'shadow' => 'bool',
                ],
                'logo' => [
                    'enable' => 'bool',
                    'file' => 'url',
                ],
                'likes' => ['enable' => 'bool'],
                'comments' => ['enable' => 'bool'],
                'views' => ['enable' => 'bool'],
                'share' => ['enable' => 'bool'],
                'see_more' => ['enable' => 'bool'],
                'author' => [
                    'enable' => 'bool',
                    'name' => 'string'
                ],
                'media' => ['enable' => 'bool'],
                'text' => ['enable' => 'bool'],
                'date' => [
                    'enable' => 'bool',
                    'format' => 'int',
                ],
                'action_links' => ['enable' => 'bool']
            ]
        ]
    ];

    static protected function getDefaultDesign ($sources)
    {
        $data = [];
        $isMultiSource = count($sources) > 1;
        if ($isMultiSource) {
            $data = [
                'post' => [
                    'border' => true,
                    'border_radius' => self::DEFAULT_BORDER_RADIUS,
                    'padding' => self::DEFAULT_PADDING,
                    'shadow' => false,
                    'image_gap' => true,
                ],
                'text' => [
                    'enable' => true,
                ],
                'action_links' => [
                    'enable' => true,
                ]
            ];
        } elseif (isset($sources[0]) && $sources[0]['vendor'] === 'instagram') {
            $data = [
                'post' => [
                    'border' => true,
                    'border_radius' => 1,
                    'padding' => self::DEFAULT_PADDING,
                    'shadow' => false,
                    'image_gap' => true,
                ],
                'text' => [
                    'enable' => false,
                ],
                'action_links' => [
                    'enable' => false,
                ]
            ];
        }

        return array_merge($data, [
            'layout' => [
                'col' => [
                    'm' => 1,
                    't' => 2,
                    'd' => 3
                ]
            ],
            'post' => [
                'border' => true,
                'border_radius' => self::DEFAULT_BORDER_RADIUS,
                'padding' => self::DEFAULT_PADDING,
                'shadow' => true,
                'image_gap' => true,
            ],
            'logo' => [
                'enable' => true,
                'file' => '',
            ],
            'likes' => ['enable' => true],
            'comments' => ['enable' => true],
            'views' => ['enable' => true],
            'share' => ['enable' => true],
            'see_more' => ['enable' => true],
            'author' => [
                'enable' => true,
                'name' => '',
            ],
            'media' => ['enable' => true],
            'text' => ['enable' => true],
            'date' => [
                'enable' => true,
                'format' => 1,
            ],
            'action_links' => ['enable' => true]
        ]);
    }
    
    /**
     * @param $feed
     * @return false|int
     */
    static public function createOrUpdateFeed($feed)
    {
        global $wpdb;

        if (isset($feed['id'])) {
            if (Validator::validateFeed($feed)) {
                DataHelper::prepareJson($feed['config']);
                $item = [
                    'name' => $feed['name'],
                    'config' => wp_json_encode($feed['config'])
                ];
                $result = $wpdb->update(CGUSF_DB_PREFIX . 'feeds', $item, ['id' => $feed['id']]);

                if ($result === false) {
                    Logger::addDbLog($result);
                } else {
                    return $feed['id'];
                }
            }
        } else {
            $sources = SourceModel::getSourceCollection($feed['source']);
            $item = [
                'name' => count($sources) > 1
                    ? __('Multi source feed')
                    : reset($sources)['info']['account']['name'] . ' - ' . ucfirst(reset($sources)['vendor']),
                'config' => wp_json_encode([
                    'type' => $feed['type'],
                    'feed_layout' => 'masonry',
                    'post_layout' => CGUSF_DEFAULT_POST_LAYOUT,
                    'slider' => false,
                    'comments' => [
                        'enable' => true,
                    ],
                    'loading' => [
                        'enable' => true,
                        'type' => 'load-btn',
                        'animation' => 'slide'
                    ],
                    'lightbox' => [
                        'enable' => true,
                    ],
                    'source' => $feed['source'],
                    'color' => [
                        'bg' => '#FFFFFFFF',
                        'bg_2' => '#FFFFFFFF',
                        'bg_3' => '#F0F0F0FF',
                        'title' => '#111111FF',
                        'text' => '#111111FF',
                        'text_2' => '#757575FF',
                        'link' => '#111111FF',
                        'border' => '#C1C1C1FF',
                    ],
                    'size' => [
                        'title' => 15,
                        'date' => 11,
                        'text' => 12,
                        'action' => 11,
                    ],
                    'design' => self::getDefaultDesign($sources),
                ])
            ];
            $result = $wpdb->insert(CGUSF_DB_PREFIX . 'feeds', $item);

            if ($result === false) {
                Logger::addDbLog($result);
            } else {
                return $wpdb->insert_id;
            }
        }

        return false;
    }

    /**
     * @param $feed
     * @return false|int
     */
    static public function saveFeedLogo($feedId, $file)
    {    
        if (!empty($feedId)) {
            $feed = self::getFeed($feedId);
            $paramFileName = sanitize_file_name($file['name']);
            $fileName = 'cgusf_feed_logo_' . $feed['id'] . '.' . pathinfo($paramFileName, PATHINFO_EXTENSION);
            $filepath = UploaderHelper::uploadFile($fileName, $file);

            if ($filepath) {  
                return $filepath;
            }
        }

        return false;
    }

    /**
     * @param $feed
     * @return false|int
     */
    static public function removeFeedLogo($feedId)
    {    
        $feed = self::getFeed($feedId);
        $filepath = UploaderHelper::removeFile($feed['config']['design']['logo']['file']);

        return self::createOrUpdateFeed($feed);
    }

    /**
     * @param $id
     * @return array[]
     */
    static public function removeFeed($id)
    {       
        if (Validator::isNumber($id)) {
            global $wpdb;

            // Remove feed
            $results = $wpdb->delete(CGUSF_DB_PREFIX . 'feeds', ['id' => $id]);

            if ($results == false) {
                Logger::addDbLog($results);
            } else {
                return Validator::getMessage('success', 'Feed have been removed');
            }
        }

        return Validator::getMessage('danger');
    }

    /**
     * @param $id
     * @return array[]
     */
    static public function duplicateFeed($id)
    {    
        if (Validator::isNumber($id)) {
            global $wpdb;
            $feed = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM ".CGUSF_DB_PREFIX."feeds WHERE id=%d LIMIT 1", $id),
                ARRAY_A
            )[0];

            if ($feed) {
                unset($feed['id']);
                $feed['name'] = $feed['name'] . ' (Copy)';
                $duplicatedFeed = $wpdb->insert(CGUSF_DB_PREFIX . 'feeds', $feed);

                if ($duplicatedFeed) {
                    return Validator::getMessage('success', 'Feed have been duplicated');
                } else {
                    Logger::addDbLog($duplicatedFeed);
                }
            } else {
                Logger::addDbLog($feed);
            }
        }

        return Validator::getMessage('danger');
    }

    /**
     * @param $id
     * @return array|false|object|void|null
     */
    static public function getFeed($id)
    {
        global $wpdb;
        $results = null;

        if (Validator::isNumber($id)) {
            $results = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM ". CGUSF_DB_PREFIX ."feeds WHERE id=%d", $id), ARRAY_A
            );

            if ($results == false) {
                Logger::addDbLog($results);
            } else {
                $results['config'] = json_decode($results['config'], true);
                DataHelper::prepareJson($results['config']);

                return $results;
            }
        }

        return Validator::getMessage('danger');
    }

    /**
     * @return array|false|object|null
     */
    static public function getFeedCollection()
    {
        global $wpdb;

        $results = $wpdb->get_results(
            "SELECT * FROM ".CGUSF_DB_PREFIX."feeds", OBJECT
        );

        if ($results == false) {
            Logger::addDbLog($results);
            return false;
        }

        return $results;
    }
}