<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    DolphinMigration  Dolphin Migration
 * @ingroup     UnaModules
 *
 * @{
 */

class BxDolMConfig extends BxBaseModGeneralConfig
{
	/**
	 *  @var array una modules uris, associated with migration modules names (mainly uses for Timeline migration) 
	 */
	public $_aModulesAliases =	array(
		'bx_store' => 'store',
		'bx_files' => 'files',		
		'bx_poll' => 'polls',
		'bx_events' => 'events',
		'bx_groups' => 'groups',		
		'bx_blogs' => 'blogs'
	);
	
	/**
	 *  @var array modules for transfer from Dolphin to una with parameters
	 *  	[table_name] -  (string) table from which to get data
	 *  	[migration_class] - (string) class name/file name for data migration
	 *  	[dependencies] -  (array) list of the modules which should be migrated before transferring selected module 
	 *  	[plugins] - (array) list of the modules which should be installed on UNA before transferring selected module
	 */
	 
	public $_aMigrationModules = array(
				'profiles' => array(
                    'table_name'     => 'Profiles', 
                    'migration_class' => 'BxDolMProfiles',
            		'dependencies' => array(
			        ),
					'plugins' => array(
						'bx_persons' => 'Persons',
			        ),
                ),
				'profile_fields' => array(
                    'table_name'		=> 'sys_profile_fields',
                    'migration_class'	=> 'BxDolMProfilesFields',
			        'dependencies' => array(
                        'profiles',
                    ),
					'plugins' => array(
						'bx_persons' => 'Persons',
			        ),
                ),				
				'blogs' => array(
                    'table_name'		=> 'bx_blogs_posts',					
                    'migration_class'	=> 'BxDolMBlogs',
					'type'				=> 'blog',
					'keywords'			=> 'bx_posts_meta_keywords',					
			        'dependencies' => array(
                		'profiles',
                     ),
					 'plugins' => array(
						'bx_persons'	=> 'Persons',
						'bx_posts'		=> 'Posts'			
			        ),
                ),				
				'groups' => array(
                    'table_name'		=> 'bx_groups_main',
                    'migration_class'	=> 'BxDolMGroups',
					'type'				=> 'bx_groups',
					'keywords'			=> 'bx_groups_meta_keywords',
			        'dependencies' => array(
                		'profiles',
                     ),
					 'plugins' => array(
						'bx_persons'	=> 'Persons',
						'bx_groups'	=> 'Groups'					
			        ),
                ),
				'events' => array(
                    'table_name'		=> 'bx_events_main',
					'type'				=> 'bx_events',
					'keywords'			=> 'bx_events_meta_keywords',					
                    'migration_class'	=> 'BxDolMEvents',					
			        'dependencies' => array(
                		'profiles',
                     ),
					 'plugins' => array(
						'bx_persons'	=> 'Persons',
						'bx_events'	=> 'Events'					
			        ),
                ),
				'photos' => array(
                    'table_name'	=> 'bx_photos_main',	
					'table_name_albums' => 'sys_albums',
					'type'				=> 'bx_photos',
					'keywords'			=> 'bx_albums_meta_keywords_media',
                    'migration_class' => 'BxDolMPhotoAlbums',
			        'dependencies' => array(
                		'profiles',
                     ),
					 'plugins' => array(
						'bx_persons'	=> 'Persons',
						'bx_albums'		=> 'Albums'	
			        ),
                ),
				'videos' => array(
                    'table_name'	=> 'RayVideoFiles',
					'table_name_albums' => 'sys_albums',
					'type'				=> 'bx_videos',
					'keywords'			=> 'bx_albums_meta_keywords_media',
                    'migration_class' => 'BxDolMVideoAlbums',
			        'dependencies' => array(
                		'profiles',
                     ),
					 'plugins' => array(
						'bx_persons'	=> 'Persons',
						'bx_albums'		=> 'Albums'	
			        ),
                ),				
				'conversations' => array(
					'table_name'		=> 'sys_messages',					
                    'migration_class'	=> 'BxDolMConv',
					'dependencies' => array(
                		'profiles',
                     ),
					 'plugins' => array(
						'bx_persons'	=> 'Persons',
						'bx_convos'	=> 'Conversations'					
			        ),
                ),
				'forum' => array(
					'table_name' => 'bx_forum_topic',
					'table_name_post' => 'bx_forum_post',
                    'migration_class'	=> 'BxDolMForum',
					'dependencies' => array(
                		'profiles',
                     ),
					 'plugins' => array(
						'bx_persons'	=> 'Persons',
						'bx_forum'	=> 'Discussions'					
			        ),
                ),
				'polls' => array(
                    'table_name'		=> 'bx_poll_data',						
                    'migration_class'	=> 'BxDolMPolls',
					'type'				=> 'bx_poll',
					'keywords'			=> 'bx_polls_meta_keywords',
			        'dependencies' => array(
                		'profiles',
                     ),
					 'plugins' => array(
						'bx_persons'	=> 'Persons',
						'bx_polls'		=> 'Polls'	
			        ),
                ),
				'files' => array(
                    'table_name'		=> 'bx_files_main',
                    'migration_class'	=> 'BxDolMFiles',
					'type'				=> 'bx_files',
					'keywords'			=> 'bx_files_meta_keywords',
			        'dependencies' => array(
                		'profiles',
                     ),
					 'plugins' => array(
						'bx_persons'	=> 'Persons',
						'bx_files'	=> 'Files'					
			        ),
                ),
				'store' => array(
                    'table_name'		=> 'bx_store_products',
                    'migration_class'	=> 'BxDolMMarket',
					'type'				=> 'bx_store',
					'keywords'			=> 'bx_market_meta_keywords',
			        'dependencies' => array(
                		'profiles',
                     ),
					 'plugins' => array(
						'bx_persons'	=> 'Persons',
						'bx_market'	=> 'Market'
			        ),
                ),
				'quotes' => array(
                    'table_name'		=> 'bx_quotes_units',
                    'migration_class'	=> 'BxDolMQuotes',			        
					 'plugins' => array(						
						'bx_quoteofday'	=> 'Quotes'
			        ),
                ),
				'shoutbox' => array(
                    'table_name'		=> 'bx_shoutbox_messages',
                    'migration_class'	=> 'BxDolMShoutBox',
			        'dependencies' => array(
                		'profiles',
                     ),
					 'plugins' => array(
						'bx_persons'	=> 'Persons',
						'bx_messenger'	=> 'Jot Messenger'
			        ),
                ),
				'simple_messenger' => array(
                    'table_name'		=> 'bx_simple_messenger_messages',
                    'migration_class'	=> 'BxDolMSimpleMessenger',
			        'dependencies' => array(
                		'profiles',
                     ),
					 'plugins' => array(
						'bx_persons'	=> 'Persons',
						'bx_messenger'	=> 'Jot Messenger'
			        ),
                ),
				'chat' => array(
                    'table_name'		=> 'RayChatHistory',
                    'migration_class'	=> 'BxDolMChat',
			        'dependencies' => array(
                		'profiles',
                     ),
					 'plugins' => array(
						'bx_persons'	=> 'Persons',
						'bx_messenger'	=> 'Jot Messenger'
			        ),
                ),
				'timeline' => array(
                    'table_name' => 'bx_wall_events',
                    'migration_class'	=> 'BxDolMTimeline',
					'dependencies' => array
					(
                		'profiles',
                    ),
					'plugins' => array
					(
						'bx_persons'	=> 'Persons',
						'bx_timeline'	=> 'Timeline'		
			        ),
                )
             );
			 
	public function __construct($aModule)
	{
		parent::__construct($aModule);		
	}

}

/** @} */
