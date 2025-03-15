<?php
$rest_api_routes = array(
    'WP_REST_Posts_Controller' => array(
        '/wp/v2/posts' => array(
            'GET' => 'Retrieve a list of posts.',
            'POST' => 'Create a new post.'
        ),
        '/wp/v2/posts/(?P<id>[\\d]+)' => array(
            'GET' => 'Retrieve a specific post by ID.',
            'POST' => 'Update a specific post by ID.',
            'DELETE' => 'Delete a specific post by ID.'
        ),
        '/wp/v2/posts/(?P<parent>[\\d]+)/revisions' => array(
            'GET' => 'Retrieve revisions for a specific post.'
        ),
        '/wp/v2/posts/(?P<parent>[\\d]+)/autosaves' => array(
            'GET' => 'Retrieve autosave revisions for a specific post.',
            'POST' => 'Create an autosave revision for a specific post.'
        )
    ),
    'WP_REST_Terms_Controller' => array(
        '/wp/v2/categories' => array(
            'GET' => 'Retrieve a list of categories.',
            'POST' => 'Create a new category.'
        ),
        '/wp/v2/categories/(?P<id>[\\d]+)' => array(
            'GET' => 'Retrieve a specific category by ID.',
            'POST' => 'Update a specific category by ID.',
            'DELETE' => 'Delete a specific category by ID.'
        ),
        '/wp/v2/tags' => array(
            'GET' => 'Retrieve a list of tags.',
            'POST' => 'Create a new tag.'
        ),
        '/wp/v2/tags/(?P<id>[\\d]+)' => array(
            'GET' => 'Retrieve a specific tag by ID.',
            'POST' => 'Update a specific tag by ID.',
            'DELETE' => 'Delete a specific tag by ID.'
        )
    ),
    'WP_REST_Users_Controller' => array(
        '/wp/v2/users' => array(
            'GET' => 'Retrieve a list of users.',
            'POST' => 'Create a new user.'
        ),
        '/wp/v2/users/(?P<id>[\\d]+)' => array(
            'GET' => 'Retrieve a specific user by ID.',
            'POST' => 'Update a specific user by ID.',
            'DELETE' => 'Delete a specific user by ID.'
        ),
        '/wp/v2/users/me' => array(
            'GET' => 'Retrieve the current authenticated user.'
        )
    ),
    'WP_REST_Comments_Controller' => array(
        '/wp/v2/comments' => array(
            'GET' => 'Retrieve a list of comments.',
            'POST' => 'Create a new comment.'
        ),
        '/wp/v2/comments/(?P<id>[\\d]+)' => array(
            'GET' => 'Retrieve a specific comment by ID.',
            'POST' => 'Update a specific comment by ID.',
            'DELETE' => 'Delete a specific comment by ID.'
        )
    ),
    'WP_REST_Search_Controller' => array(
        '/wp/v2/search' => array(
            'GET' => 'Perform a search query.'
        )
    ),
    'WP_REST_Settings_Controller' => array(
        '/wp/v2/settings' => array(
            'GET' => 'Retrieve site settings.',
            'POST' => 'Update site settings.'
        )
    ),
    'WP_REST_Revisions_Controller' => array(
        '/wp/v2/posts/(?P<parent>[\\d]+)/revisions' => array(
            'GET' => 'Retrieve revisions for a specific post.'
        ),
        '/wp/v2/posts/(?P<parent>[\\d]+)/revisions/(?P<id>[\\d]+)' => array(
            'GET' => 'Retrieve a specific revision by ID.',
            'DELETE' => 'Delete a specific revision by ID.'
        )
    ),
    'WP_REST_Attachments_Controller' => array(
        '/wp/v2/media' => array(
            'GET' => 'Retrieve a list of media items.',
            'POST' => 'Upload a new media item.'
        ),
        '/wp/v2/media/(?P<id>[\\d]+)' => array(
            'GET' => 'Retrieve a specific media item by ID.',
            'POST' => 'Update a specific media item by ID.',
            'DELETE' => 'Delete a specific media item by ID.'
        )
    ),
    'WP_REST_Block_Renderer_Controller' => array(
        '/wp/v2/block-renderer/(?P<name>[a-zA-Z0-9-]+)' => array(
            'GET' => 'Render a specific block.'
        )
    ),
    'WP_REST_Block_Pattern_Categories_Controller' => array(
        '/wp/v2/block-patterns/pattern-categories' => array(
            'GET' => 'Retrieve a list of block pattern categories.'
        )
    ),
    'WP_REST_Block_Patterns_Controller' => array(
        '/wp/v2/block-patterns/patterns' => array(
            'GET' => 'Retrieve a list of block patterns.'
        )
    ),
    'WP_REST_Block_Types_Controller' => array(
        '/wp/v2/block-types' => array(
            'GET' => 'Retrieve a list of block types.'
        ),
        '/wp/v2/block-types/(?P<name>[a-zA-Z0-9-]+)' => array(
            'GET' => 'Retrieve a specific block type by name.'
        )
    ),
    'WP_REST_Taxonomies_Controller' => array(
        '/wp/v2/taxonomies' => array(
            'GET' => 'Retrieve a list of taxonomies.'
        ),
        '/wp/v2/taxonomies/(?P<taxonomy>[a-zA-Z0-9-_]+)' => array(
            'GET' => 'Retrieve a specific taxonomy by name.'
        )
    ),
    'WP_REST_Menu_Items_Controller' => array(
        '/wp/v2/menu-items' => array(
            'GET' => 'Retrieve a list of menu items.',
            'POST' => 'Create a new menu item.'
        ),
        '/wp/v2/menu-items/(?P<id>[\\d]+)' => array(
            'GET' => 'Retrieve a specific menu item by ID.',
            'POST' => 'Update a specific menu item by ID.',
            'DELETE' => 'Delete a specific menu item by ID.'
        )
    ),
    'WP_REST_Menu_Locations_Controller' => array(
        '/wp/v2/menu-locations' => array(
            'GET' => 'Retrieve a list of menu locations.'
        ),
        '/wp/v2/menu-locations/(?P<location>[a-zA-Z0-9-_]+)' => array(
            'GET' => 'Retrieve a specific menu location by name.'
        )
    ),
    'WP_REST_Menus_Controller' => array(
        '/wp/v2/menus' => array(
            'GET' => 'Retrieve a list of navigation menus.',
            'POST' => 'Create a new navigation menu.'
        ),
        '/wp/v2/menus/(?P<id>[\\d]+)' => array(
            'GET' => 'Retrieve a specific navigation menu by ID.',
            'POST' => 'Update a specific navigation menu by ID.',
            'DELETE' => 'Delete a specific navigation menu by ID.'
        )
    ),
    'WP_REST_Global_Styles_Controller' => array(
        '/wp/v2/global-styles/(?P<id>[\\w-]+)' => array(
            'GET' => 'Retrieve global styles for a specific theme or block.',
            'POST' => 'Update global styles for a specific theme or block.'
        ),
        '/wp/v2/global-styles/themes/(?P<stylesheet>[\\w-]+)' => array(
            'GET' => 'Retrieve global styles specific to a theme.'
        )
    ),
    'WP_REST_Themes_Controller' => array(
        '/wp/v2/themes' => array(
            'GET' => 'Retrieve a list of available themes.'
        ),
        '/wp/v2/themes/(?P<stylesheet>[\\w-]+)' => array(
            'GET' => 'Retrieve details of a specific theme.',
            'POST' => 'Activate a specific theme.'
        )
    ),
    'WP_REST_Autosaves_Controller' => array(
        '/wp/v2/posts/(?P<parent>[\\d]+)/autosaves' => array(
            'GET' => 'Retrieve autosave revisions for a specific post.',
            'POST' => 'Create or update an autosave revision.'
        ),
        '/wp/v2/posts/(?P<parent>[\\d]+)/autosaves/(?P<id>[\\d]+)' => array(
            'GET' => 'Retrieve a specific autosave revision by ID.'
        )
    ),
    'WP_REST_Application_Passwords_Controller' => array(
        '/wp/v2/users/(?P<user_id>[\\d]+)/application-passwords' => array(
            'GET' => 'Retrieve application passwords for a specific user.',
            'POST' => 'Create a new application password for a user.'
        ),
        '/wp/v2/users/(?P<user_id>[\\d]+)/application-passwords/(?P<uuid>[\\w-]+)' => array(
            'GET' => 'Retrieve details of a specific application password.',
            'DELETE' => 'Revoke an application password.'
        )
    ),
    'WP_REST_Sidebars_Controller' => array(
        '/wp/v2/sidebars' => array(
            'GET' => 'Retrieve a list of registered sidebars.'
        ),
        '/wp/v2/sidebars/(?P<id>[\\w-]+)' => array(
            'GET' => 'Retrieve details of a specific sidebar.'
        )
    ),
    'WP_REST_Widgets_Controller' => array(
        '/wp/v2/widgets' => array(
            'GET' => 'Retrieve a list of widgets.',
            'POST' => 'Add a new widget.'
        ),
        '/wp/v2/widgets/(?P<id>[\\w-]+)' => array(
            'GET' => 'Retrieve details of a specific widget.',
            'POST' => 'Update a specific widget.',
            'DELETE' => 'Delete a specific widget.'
        )
    ),
    'WP_REST_Theme_JSON_Controller' => array(
        '/wp/v2/theme-json' => array(
            'GET' => 'Retrieve the active theme.json configuration.',
            'POST' => 'Update the theme.json configuration.'
        )
    ),
    'WP_REST_URL_Details_Controller' => array(
        '/wp/v2/url-details' => array(
            'GET' => 'Retrieve metadata and details about a given URL.'
        )
    )
);
