<?php
namespace WPZOOMElementorWidgets;

use Elementor\Widget_Base;
use Elementor\Group_Control_Background;
use Elementor\Repeater;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Scheme_Typography;
use Elementor\Plugin;
use Elementor\Utils;
use Elementor\Embed;
use Elementor\Icons_Manager;
use Elementor\Modules\DynamicTags\Module as TagsModule;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * ZOOM Elementor Widgets - Slider Widget.
 *
 * Elementor widget that inserts a customizable slider.
 *
 * @since 1.0.0
 */
class Slider extends Widget_Base {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );

		if ( ! wp_style_is( 'slick-slider', 'registered' ) ) {
			wp_register_style( 'slick-slider', plugins_url( 'assets/slick/slick.css', dirname( __FILE__, 2 ) ), null, '1.0.0' );
		}

		if ( ! wp_style_is( 'slick-slider-theme', 'registered' ) ) {
			wp_register_style( 'slick-slider-theme', plugins_url( 'assets/slick/slick-theme.css', dirname( __FILE__, 2 ) ), null, '1.0.0' );
		}

		wp_register_style( 'wpzoom-elementor-widgets-css-frontend-slider', plugins_url( 'frontend.css', __FILE__ ), [ 'slick-slider', 'slick-slider-theme' ], '1.0.0' );

		if ( ! wp_script_is( 'jquery-slick-slider', 'registered' ) ) {
			wp_register_script( 'jquery-slick-slider', plugins_url( 'assets/slick/slick.min.js', dirname( __FILE__, 2 ) ), [ 'jquery' ], '1.0.0', true );
		}

		wp_register_script( 'wpzoom-elementor-widgets-js-frontend-slider', plugins_url( 'frontend.js', __FILE__ ), [ 'jquery', 'jquery-slick-slider' ], '1.0.0', true );
	}

	/**
	 * Get widget name.
	 *
	 * Retrieve widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'wpzoom-elementor-widgets-slider';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Slider', 'zoom-elementor-widgets' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-slides';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'wpzoom-elementor-widgets' ];
	}

	/**
	 * Style Dependencies.
	 *
	 * Returns all the styles the widget depends on.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Style slugs.
	 */
	public function get_style_depends() {
		return [
			'slick-slider',
			'slick-slider-theme',
			'font-awesome-5-all',
			'font-awesome-4-shim',
			'wpzoom-elementor-widgets-css-frontend-slider'
		];
	}

	/**
	 * Script Dependencies.
	 *
	 * Returns all the scripts the widget depends on.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Script slugs.
	 */
	public function get_script_depends() {
		return [
			'jquery',
			'jquery-slick-slider',
			'font-awesome-4-shim',
			'wpzoom-elementor-widgets-js-frontend-slider'
		];
	}

	/**
	 * Get All Post Types.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array An array of all currently-registered post types.
	 */
	public function get_post_types() {
		$post_types = get_post_types( [ 'public' => true, 'show_in_nav_menus' => true ], 'objects' );
		$post_types = wp_list_pluck( $post_types, 'label', 'name' );

		return array_diff_key( $post_types, [ 'elementor_library', 'attachment' ] );
	}

	/**
	 * Get All Posts of Type.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param  string $post_type The post type to get all posts for.
	 * @param  int    $limit     Limit to the number of posts returned.
	 * @return array             An array of all posts in the given post type.
	 */
	public function get_post_list( $post_type = 'any', $limit = -1 ) {
		global $wpdb;

		$where = '';
		$data = [];

		if ( -1 == $limit ) {
			$limit = '';
		} elseif ( 0 == $limit ) {
			$limit = "limit 0,1";
		} else {
			$limit = $wpdb->prepare( " limit 0,%d", esc_sql( $limit ) );
		}

		if ( 'any' === $post_type ) {
			$in_search_post_types = get_post_types( [ 'exclude_from_search' => false ] );

			if ( empty( $in_search_post_types ) ) {
				$where .= ' AND 1=0 ';
			} else {
				$where .= " AND {$wpdb->posts}.post_type IN ('" . join( "', '", array_map( 'esc_sql', $in_search_post_types ) ) . "')";
			}
		} elseif ( ! empty( $post_type ) ) {
			$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_type = %s", esc_sql( $post_type ) );
		}

		if ( ! empty( $search ) ) {
			$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_title LIKE %s", '%' . esc_sql( $search ) . '%' );
		}

		$query = "select post_title,ID  from $wpdb->posts where post_status = 'publish' $where $limit";
		$results = $wpdb->get_results( $query );

		if ( ! empty( $results ) ) {
			foreach ( $results as $row ) {
				$data[ $row->ID ] = $row->post_title;
			}
		}

		return $data;
	}

	/**
	 * Get All Authors.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array An array of all registered authors.
	 */
	public function get_authors_list() {
		$users = get_users( [
			'who' => 'authors',
			'has_published_posts' => true,
			'fields' => [
				'ID',
				'display_name'
			]
		] );

		if ( ! empty( $users ) ) {
			return wp_list_pluck( $users, 'display_name', 'ID' );
		}

		return [];
	}

	/**
	 * Post Order-By Options.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array An array of all Order-By options for querying posts.
	 */
	public function get_post_orderby_options() {
		return [
			'ID'            => __( 'Post ID', 'zoom-elementor-widgets' ),
			'author'        => __( 'Post Author', 'zoom-elementor-widgets' ),
			'title'         => __( 'Title', 'zoom-elementor-widgets' ),
			'date'          => __( 'Date', 'zoom-elementor-widgets' ),
			'modified'      => __( 'Last Modified Date', 'zoom-elementor-widgets' ),
			'parent'        => __( 'Parent ID', 'zoom-elementor-widgets' ),
			'rand'          => __( 'Random', 'zoom-elementor-widgets' ),
			'comment_count' => __( 'Comment Count', 'zoom-elementor-widgets' ),
			'menu_order'    => __( 'Menu Order', 'zoom-elementor-widgets' )
		];
	}

	/**
	 * Get Post Terms.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array An array of all terms in the given taxonomy.
	 */
	public function get_terms_list( $taxonomy = 'category', $key = 'term_id' ) {
		$options = [];
		$terms = get_terms( [
			'taxonomy' => $taxonomy,
			'hide_empty' => true
		] );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->{$key} ] = $term->name;
			}
		}

		return $options;
	}

	/**
	 * Register Controls.
	 *
	 * Registers all the controls for this widget.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function _register_controls() {
		$this->register_content_controls();
		$this->register_style_controls();
	}

	/**
	 * Register Content Controls.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function register_content_controls() {
		$this->start_controls_section(
			'_section_slides',
			[
				'label' => __( 'Slides', 'zoom-elementor-widgets' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'slides_source',
			[
				'label' => __( 'Source', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'custom',
				'options' => [
					'custom' => __( 'Custom', 'zoom-elementor-widgets' ),
					'posts' => __( 'WordPress Posts', 'zoom-elementor-widgets' )
				],
				'separator' => 'after'
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'image',
			[
				'type' => Controls_Manager::MEDIA,
				'label' => __( 'Image', 'zoom-elementor-widgets' ),
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
				'dynamic' => [
					'active' => true,
				]
			]
		);

		$repeater->add_control(
			'video',
			[
				'type' => Controls_Manager::POPOVER_TOGGLE,
				'label' => __( 'Video', 'zoom-elementor-widgets' ),
				'label_off' => __( 'None', 'zoom-elementor-widgets' ),
				'label_on' => __( 'Custom', 'zoom-elementor-widgets' ),
				'return_value' => 'yes',
				'frontend_available' => true
			]
		);

		$repeater->start_popover();

		$repeater->add_control(
			'video_type',
			[
				'label' => __( 'Source', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SELECT,
				'condition' => [
					'video' => 'yes'
				],
				'default' => 'youtube',
				'options' => [
					'youtube' => __( 'YouTube', 'zoom-elementor-widgets' ),
					'vimeo' => __( 'Vimeo', 'zoom-elementor-widgets' ),
					'dailymotion' => __( 'Dailymotion', 'zoom-elementor-widgets' ),
					'hosted' => __( 'Self Hosted', 'zoom-elementor-widgets' )
				],
				'frontend_available' => true
			]
		);

		$repeater->add_control(
			'youtube_url',
			[
				'label' => __( 'Link', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY
					]
				],
				'placeholder' => __( 'Enter your URL', 'zoom-elementor-widgets' ) . ' (YouTube)',
				'default' => 'https://www.youtube.com/watch?v=XHOmBV4js_E',
				'label_block' => true,
				'condition' => [
					'video' => 'yes',
					'video_type' => 'youtube'
				],
				'frontend_available' => true
			]
		);

		$repeater->add_control(
			'vimeo_url',
			[
				'label' => __( 'Link', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY
					]
				],
				'placeholder' => __( 'Enter your URL', 'zoom-elementor-widgets' ) . ' (Vimeo)',
				'default' => 'https://vimeo.com/235215203',
				'label_block' => true,
				'condition' => [
					'video' => 'yes',
					'video_type' => 'vimeo'
				],
				'frontend_available' => true
			]
		);

		$repeater->add_control(
			'dailymotion_url',
			[
				'label' => __( 'Link', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY
					]
				],
				'placeholder' => __( 'Enter your URL', 'zoom-elementor-widgets' ) . ' (Dailymotion)',
				'default' => 'https://www.dailymotion.com/video/x6tqhqb',
				'label_block' => true,
				'condition' => [
					'video' => 'yes',
					'video_type' => 'dailymotion'
				],
				'frontend_available' => true
			]
		);

		$repeater->add_control(
			'insert_url',
			[
				'label' => __( 'External URL', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'condition' => [
					'video' => 'yes',
					'video_type' => 'hosted'
				]
			]
		);

		$repeater->add_control(
			'hosted_url',
			[
				'label' => __( 'Choose File', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::MEDIA,
				'dynamic' => [
					'active' => true,
					'categories' => [
						TagsModule::MEDIA_CATEGORY
					]
				],
				'media_type' => 'video',
				'condition' => [
					'video' => 'yes',
					'video_type' => 'hosted',
					'insert_url' => ''
				],
				'frontend_available' => true
			]
		);

		$repeater->add_control(
			'external_url',
			[
				'label' => __( 'URL', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::URL,
				'autocomplete' => false,
				'options' => false,
				'label_block' => true,
				'show_label' => false,
				'dynamic' => [
					'active' => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY
					]
				],
				'media_type' => 'video',
				'placeholder' => __( 'Enter your URL', 'zoom-elementor-widgets' ),
				'condition' => [
					'video' => 'yes',
					'video_type' => 'hosted',
					'insert_url' => 'yes'
				],
				'frontend_available' => true
			]
		);

		$repeater->end_popover();

		$repeater->add_control(
			'title',
			[
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'label' => __( 'Title', 'zoom-elementor-widgets' ),
				'placeholder' => __( 'Type title here', 'zoom-elementor-widgets' ),
				'dynamic' => [
					'active' => true,
				]
			]
		);

		$repeater->add_control(
			'subtitle',
			[
				'label' => __( 'Subtitle', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::TEXTAREA,
				'label_block' => true,
				'placeholder' => __( 'Type subtitle here', 'zoom-elementor-widgets' ),
				'dynamic' => [
					'active' => true,
				]
			]
		);

		$repeater->add_control(
			'link',
			[
				'label' => __( 'Link', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::URL,
				'label_block' => true,
				'placeholder' => 'https://example.com',
				'dynamic' => [
					'active' => true,
				]
			]
		);

		$placeholder = [
			'image' => [
				'url' => Utils::get_placeholder_image_src(),
			],
		];

		$this->add_control(
			'slides',
			[
				'show_label' => false,
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'title_field' => '<# print(title || "Slider Item"); #>',
				'default' => array_fill( 0, 7, $placeholder ),
				'condition' => [
					'slides_source' => 'custom'
				]
			]
		);

		$post_types = $this->get_post_types();
		$post_types[ 'by_id' ] = __( 'Manual Selection', 'zoom-elementor-widgets' );
		$post_list = $this->get_post_list();
		$author_list = $this->get_authors_list();
		$taxonomies = get_taxonomies( [], 'objects' );
		$orderby_options = $this->get_post_orderby_options();

		$this->add_control(
			'posts_type',
			[
				'label' => __( 'Posts Source', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SELECT,
				'options' => $post_types,
				'default' => key( $post_types ),
				'condition' => [
					'slides_source' => 'posts'
				]
			]
		);

		$this->add_control(
			'posts_ids',
			[
				'label' => __( 'Search & Select', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SELECT2,
				'options' => $post_list,
				'label_block' => true,
				'multiple' => true,
				'condition' => [
					'slides_source' => 'posts',
					'posts_type' => 'by_id'
				]
			]
		);

		$this->add_control(
			'posts_authors', [
				'label' => __( 'Author', 'zoom-elementor-widgets' ),
				'label_block' => true,
				'type' => Controls_Manager::SELECT2,
				'multiple' => true,
				'default' => [],
				'options' => $author_list,
				'condition' => [
					'slides_source' => 'posts',
					'posts_type!' => [ 'by_id' ]
				]
			]
		);

		foreach ( $taxonomies as $taxonomy => $object ) {
			if ( ! isset( $object->object_type[0] ) || ! in_array( $object->object_type[0], array_keys( $post_types ) ) ) {
				continue;
			}

			$this->add_control(
				'posts_' . $taxonomy . '_ids',
				[
					'label' => $object->label,
					'type' => Controls_Manager::SELECT2,
					'label_block' => true,
					'multiple' => true,
					'object_type' => $taxonomy,
					'options' => wp_list_pluck( get_terms( $taxonomy ), 'name', 'term_id' ),
					'condition' => [
						'slides_source' => 'posts',
						'posts_type' => $object->object_type
					]
				]
			);
		}

		$this->add_control(
			'posts__not_in',
			[
				'label' => __( 'Exclude', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SELECT2,
				'options' => $post_list,
				'label_block' => true,
				'post_type' => '',
				'multiple' => true,
				'condition' => [
					'slides_source' => 'posts',
					'posts_type!' => [ 'by_id' ]
				]
			]
		);

		$this->add_control(
			'posts_offset',
			[
				'label' => __( 'Offset', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::NUMBER,
				'default' => '0',
				'condition' => [
					'slides_source' => 'posts'
				]
			]
		);

		$this->add_control(
			'posts_orderby',
			[
				'label' => __( 'Order By', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SELECT,
				'options' => $orderby_options,
				'default' => 'date',
				'condition' => [
					'slides_source' => 'posts'
				]
			]
		);

		$this->add_control(
			'posts_order',
			[
				'label' => __( 'Order', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'asc' => __( 'Ascending', 'zoom-elementor-widgets' ),
					'desc' => __( 'Descending', 'zoom-elementor-widgets' ),
				],
				'default' => 'desc',
				'condition' => [
					'slides_source' => 'posts'
				]
			]
		);

		$this->add_control(
			'posts_amount',
			[
				'label' => __( 'Amount', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::NUMBER,
				'min' => 1,
				'step' => 1,
				'max' => 100,
				'default' => 5,
				'condition' => [
					'slides_source' => 'posts'
				]
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name' => 'thumbnail',
				'default' => 'medium_large',
				'separator' => 'before',
				'exclude' => [
					'custom'
				]
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_settings',
			[
				'label' => __( 'Settings', 'zoom-elementor-widgets' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'settings_slides',
			[
				'label' => __( 'Slides', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::HEADING
			]
		);

		$this->add_control(
			'animation_speed',
			[
				'label' => __( 'Animation Speed', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::NUMBER,
				'min' => 100,
				'step' => 10,
				'max' => 10000,
				'default' => 300,
				'description' => __( 'Slide speed in milliseconds', 'zoom-elementor-widgets' ),
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label' => __( 'Autoplay?', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'zoom-elementor-widgets' ),
				'label_off' => __( 'No', 'zoom-elementor-widgets' ),
				'return_value' => 'yes',
				'default' => 'yes',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'autoplay_speed',
			[
				'label' => __( 'Autoplay Speed', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::NUMBER,
				'min' => 100,
				'step' => 100,
				'max' => 10000,
				'default' => 3000,
				'description' => __( 'Autoplay speed in milliseconds', 'zoom-elementor-widgets' ),
				'condition' => [
					'autoplay' => 'yes'
				],
				'frontend_available' => true
			]
		);

		$this->add_control(
			'loop',
			[
				'label' => __( 'Infinite Loop?', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'zoom-elementor-widgets' ),
				'label_off' => __( 'No', 'zoom-elementor-widgets' ),
				'return_value' => 'yes',
				'default' => 'yes',
				'frontend_available' => true
			]
		);

		$this->add_control(
			'center',
			[
				'label' => __( 'Center Mode?', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'zoom-elementor-widgets' ),
				'label_off' => __( 'No', 'zoom-elementor-widgets' ),
				'return_value' => 'yes',
				'description' => __( 'Best works with odd number of slides (Slides To Show) and loop (Infinite Loop)', 'zoom-elementor-widgets' ),
				'frontend_available' => true,
				'style_transfer' => true
			]
		);

		$this->add_control(
			'vertical',
			[
				'label' => __( 'Vertical Mode?', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'zoom-elementor-widgets' ),
				'label_off' => __( 'No', 'zoom-elementor-widgets' ),
				'return_value' => 'yes',
				'frontend_available' => true,
				'style_transfer' => true
			]
		);

		$this->add_responsive_control(
			'slides_to_show',
			[
				'label' => __( 'Slides To Show', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					1 => __( '1 Slide', 'zoom-elementor-widgets' ),
					2 => __( '2 Slides', 'zoom-elementor-widgets' ),
					3 => __( '3 Slides', 'zoom-elementor-widgets' ),
					4 => __( '4 Slides', 'zoom-elementor-widgets' ),
					5 => __( '5 Slides', 'zoom-elementor-widgets' ),
					6 => __( '6 Slides', 'zoom-elementor-widgets' )
				],
				'desktop_default' => 1,
				'tablet_default' => 1,
				'mobile_default' => 1,
				'frontend_available' => true,
				'style_transfer' => true
			]
		);

		$this->add_control(
			'settings_video',
			[
				'label' => __( 'Video', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before'
			]
		);

		$this->add_control(
			'video_autoplay',
			[
				'label' => __( 'Autoplay', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'frontend_available' => true
			]
		);

		$this->add_control(
			'play_on_mobile',
			[
				'label' => __( 'Play On Mobile', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'condition' => [
					'video_autoplay' => 'yes'
				],
				'frontend_available' => true
			]
		);

		$this->add_control(
			'video_mute',
			[
				'label' => __( 'Mute', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'frontend_available' => true
			]
		);

		$this->add_control(
			'video_loop',
			[
				'label' => __( 'Loop', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'frontend_available' => true
			]
		);

		$this->add_control(
			'video_controls',
			[
				'label' => __( 'Player Controls', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => __( 'Hide', 'zoom-elementor-widgets' ),
				'label_on' => __( 'Show', 'zoom-elementor-widgets' ),
				'frontend_available' => true
			]
		);

		$this->add_control(
			'video_showinfo',
			[
				'label' => __( 'Video Info', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => __( 'Hide', 'zoom-elementor-widgets' ),
				'label_on' => __( 'Show', 'zoom-elementor-widgets' ),
				'frontend_available' => true
			]
		);

		$this->add_control(
			'video_modestbranding',
			[
				'label' => __( 'Modest Branding', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'condition' => [
					'video_controls' => 'yes'
				],
				'frontend_available' => true
			]
		);

		$this->add_control(
			'video_logo',
			[
				'label' => __( 'Logo', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => __( 'Hide', 'zoom-elementor-widgets' ),
				'label_on' => __( 'Show', 'zoom-elementor-widgets' ),
				'frontend_available' => true
			]
		);

		$this->add_control(
			'yt_privacy',
			[
				'label' => __( 'Privacy Mode', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'description' => __( 'When you turn on privacy mode, YouTube won\'t store information about visitors on your website unless they play the video.', 'zoom-elementor-widgets' ),
				'frontend_available' => true
			]
		);

		$this->add_control(
			'video_rel',
			[
				'label' => __( 'Suggested Videos', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'' => __( 'Current Video Channel', 'zoom-elementor-widgets' ),
					'yes' => __( 'Any Video', 'zoom-elementor-widgets' )
				],
				'frontend_available' => true
			]
		);

		$this->add_control(
			'vimeo_title',
			[
				'label' => __( 'Intro Title', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => __( 'Hide', 'zoom-elementor-widgets' ),
				'label_on' => __( 'Show', 'zoom-elementor-widgets' ),
				'frontend_available' => true
			]
		);

		$this->add_control(
			'vimeo_portrait',
			[
				'label' => __( 'Intro Portrait', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => __( 'Hide', 'zoom-elementor-widgets' ),
				'label_on' => __( 'Show', 'zoom-elementor-widgets' ),
				'default' => 'yes',
				'frontend_available' => true
			]
		);

		$this->add_control(
			'vimeo_byline',
			[
				'label' => __( 'Intro Byline', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => __( 'Hide', 'zoom-elementor-widgets' ),
				'label_on' => __( 'Show', 'zoom-elementor-widgets' ),
				'frontend_available' => true
			]
		);

		$this->add_control(
			'video_download_button',
			[
				'label' => __( 'Download Button', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => __( 'Hide', 'zoom-elementor-widgets' ),
				'label_on' => __( 'Show', 'zoom-elementor-widgets' ),
				'frontend_available' => true
			]
		);

		$this->add_control(
			'video_poster',
			[
				'label' => __( 'Poster', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::MEDIA,
				'dynamic' => [
					'active' => true
				],
				'frontend_available' => true
			]
		);

		$this->add_control(
			'show_play_icon',
			[
				'label' => __( 'Play Icon', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes'
			]
		);

		$this->add_control(
			'settings_navigation',
			[
				'label' => __( 'Navigation', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before'
			]
		);

		$this->add_control(
			'navigation',
			[
				'label' => __( 'Navigation', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'none' => __( 'None', 'zoom-elementor-widgets' ),
					'arrow' => __( 'Arrow', 'zoom-elementor-widgets' ),
					'dots' => __( 'Dots', 'zoom-elementor-widgets' ),
					'both' => __( 'Arrow & Dots', 'zoom-elementor-widgets' )
				],
				'default' => 'arrow',
				'frontend_available' => true,
				'style_transfer' => true,
			]
		);

		$this->add_control(
			'arrow_prev_icon',
			[
				'label' => __( 'Previous Icon', 'zoom-elementor-widgets' ),
				'label_block' => false,
				'type' => Controls_Manager::ICONS,
				'skin' => 'inline',
				'default' => [
					'value' => 'fas fa-chevron-left',
					'library' => 'fa-solid'
				],
				'condition' => [
					'navigation' => [ 'arrow', 'both' ]
				],
			]
		);

		$this->add_control(
			'arrow_next_icon',
			[
				'label' => __( 'Next Icon', 'zoom-elementor-widgets' ),
				'label_block' => false,
				'type' => Controls_Manager::ICONS,
				'skin' => 'inline',
				'default' => [
					'value' => 'fas fa-chevron-right',
					'library' => 'fa-solid'
				],
				'condition' => [
					'navigation' => [ 'arrow', 'both' ]
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Register Style Controls.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function register_style_controls() {
		$this->start_controls_section(
			'_section_style_slider',
			[
				'label' => __( 'Slider', 'zoom-elementor-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'auto_height',
			[
				'label' => __( 'Automatic Height', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes'
			]
		);

		$this->add_responsive_control(
			'auto_height_size',
			[
				'label' => __( 'Automatic Height Size', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range' => [
					'%' => [
						'min' => 1,
						'max' => 100,
					]
				],
				'default' => [
					'unit' => '%',
					'size' => 100
				],
				'selectors' => [
					'{{WRAPPER}} .slick-slider' => 'height: {{SIZE}}vh;'
				],
				'condition' => [
					'auto_height' => 'yes'
				]
			]
		);

		$this->add_responsive_control(
			'auto_height_max',
			[
				'label' => __( 'Automatic Height Maximum', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'%' => [
						'min' => 1,
						'max' => 100,
					]
				],
				'default' => [
					'unit' => 'px',
					'size' => 550
				],
				'desktop_default' => [
					'unit' => 'px',
					'size' => 550
				],
				'tablet_default' => [
					'unit' => 'px',
					'size' => 350
				],
				'mobile_default' => [
					'unit' => 'px',
					'size' => 250
				],
				'selectors' => [
					'{{WRAPPER}} .slick-slider' => 'max-height: {{SIZE}}{{UNIT}};'
				],
				'condition' => [
					'auto_height' => 'yes'
				]
			]
		);

		$this->end_controls_section();
		
		$this->start_controls_section(
			'_section_style_item',
			[
				'label' => __( 'Slider Item', 'zoom-elementor-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'item_spacing',
			[
				'label' => __( 'Slide Spacing (px)', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'default' => [
					'unit' => 'px',
					'size' => 0,
				],
				'selectors' => [
					'{{WRAPPER}} .slick-slider:not(.slick-vertical) .slick-slide' => 'padding-right: {{SIZE}}{{UNIT}}; padding-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .slick-slider.slick-vertical .slick-slide' => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_border_radius',
			[
				'label' => __( 'Border Radius', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .zew-slick-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_style_content',
			[
				'label' => __( 'Slide Content', 'zoom-elementor-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'content_padding',
			[
				'label' => __( 'Content Padding', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .zew-slick-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'content_background',
				'selector' => '{{WRAPPER}} .zew-slick-content',
				'exclude' => [
					 'image'
				]
			]
		);

		$this->add_control(
			'_heading_title',
			[
				'type' => Controls_Manager::HEADING,
				'label' => __( 'Title', 'zoom-elementor-widgets' ),
				'separator' => 'before'
			]
		);

		$this->add_responsive_control(
			'title_spacing',
			[
				'label' => __( 'Bottom Spacing', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .zew-slick-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => __( 'Text Color', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .zew-slick-title' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title',
				'label' => __( 'Typography', 'zoom-elementor-widgets' ),
				'selector' => '{{WRAPPER}} .zew-slick-title',
				'scheme' => Scheme_Typography::TYPOGRAPHY_2,
			]
		);

		$this->add_control(
			'_heading_subtitle',
			[
				'type' => Controls_Manager::HEADING,
				'label' => __( 'Subtitle', 'zoom-elementor-widgets' ),
				'separator' => 'before'
			]
		);

		$this->add_responsive_control(
			'subtitle_spacing',
			[
				'label' => __( 'Bottom Spacing', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .zew-slick-subtitle' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'subtitle_color',
			[
				'label' => __( 'Text Color', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .zew-slick-subtitle' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'subtitle',
				'label' => __( 'Typography', 'zoom-elementor-widgets' ),
				'selector' => '{{WRAPPER}} .zew-slick-subtitle',
				'scheme' => Scheme_Typography::TYPOGRAPHY_3,
			]
		);

		$this->add_control(
			'_heading_video',
			[
				'type' => Controls_Manager::HEADING,
				'label' => __( 'Video', 'zoom-elementor-widgets' ),
				'separator' => 'before'
			]
		);

		$this->add_control(
			'aspect_ratio',
			[
				'label' => __( 'Aspect Ratio', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'169' => '16:9',
					'219' => '21:9',
					'43' => '4:3',
					'32' => '3:2',
					'11' => '1:1',
					'916' => '9:16'
				],
				'default' => '169',
				'prefix_class' => 'elementor-aspect-ratio-',
				'frontend_available' => true
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name' => 'css_filters',
				'selector' => '{{WRAPPER}} .elementor-wrapper'
			]
		);

		$this->add_control(
			'video_controls_color',
			[
				'label' => __( 'Controls Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => ''
			]
		);

		$this->add_control(
			'play_icon_color',
			[
				'label' => __( 'Play Icon Color', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-custom-embed-play i' => 'color: {{VALUE}}'
				],
				'condition' => [
					'show_play_icon' => 'yes'
				]
			]
		);

		$this->add_responsive_control(
			'play_icon_size',
			[
				'label' => __( 'Play Icon Size', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 300
					]
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-custom-embed-play i' => 'font-size: {{SIZE}}{{UNIT}}'
				],
				'condition' => [
					'show_play_icon' => 'yes'
				]
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'play_icon_text_shadow',
				'selector' => '{{WRAPPER}} .elementor-custom-embed-play i',
				'fields_options' => [
					'text_shadow_type' => [
						'label' => _x( 'Play Icon Shadow', 'Text Shadow Control', 'zoom-elementor-widgets' )
					]
				],
				'condition' => [
					'show_play_icon' => 'yes'
				]
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_style_arrow',
			[
				'label' => __( 'Navigation :: Arrow', 'zoom-elementor-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'arrow_position_toggle',
			[
				'label' => __( 'Position', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::POPOVER_TOGGLE,
				'label_off' => __( 'None', 'zoom-elementor-widgets' ),
				'label_on' => __( 'Custom', 'zoom-elementor-widgets' ),
				'return_value' => 'yes',
			]
		);

		$this->start_popover();

		$this->add_responsive_control(
			'arrow_position_y',
			[
				'label' => __( 'Vertical', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'condition' => [
					'arrow_position_toggle' => 'yes'
				],
				'range' => [
					'px' => [
						'min' => -100,
						'max' => 500,
					],
					'%' => [
						'min' => -110,
						'max' => 110,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .slick-prev, {{WRAPPER}} .slick-next' => 'top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'arrow_position_x',
			[
				'label' => __( 'Horizontal', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'default' => [
					'unit' => 'px',
					'size' => 25,
				],
				'condition' => [
					'arrow_position_toggle' => 'yes'
				],
				'range' => [
					'px' => [
						'min' => -100,
						'max' => 500,
					],
					'%' => [
						'min' => -110,
						'max' => 110,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .slick-prev' => 'left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .slick-next' => 'right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_popover();

		$this->add_responsive_control(
			'arrow_size',
			[
				'label' => __( 'Size', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'default' => [
					'unit' => 'px',
					'size' => 40,
				],
				'selectors' => [
					'{{WRAPPER}} .slick-prev, {{WRAPPER}} .slick-next' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'arrow_border',
				'selector' => '{{WRAPPER}} .slick-prev, {{WRAPPER}} .slick-next',
			]
		);

		$this->add_responsive_control(
			'arrow_border_radius',
			[
				'label' => __( 'Border Radius', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .slick-prev, {{WRAPPER}} .slick-next' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->start_controls_tabs( '_tabs_arrow' );

		$this->start_controls_tab(
			'_tab_arrow_normal',
			[
				'label' => __( 'Normal', 'zoom-elementor-widgets' ),
			]
		);

		$this->add_control(
			'arrow_color',
			[
				'label' => __( 'Color', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .slick-prev, {{WRAPPER}} .slick-next' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'arrow_bg_color',
			[
				'label' => __( 'Background Color', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#00000000',
				'selectors' => [
					'{{WRAPPER}} .slick-prev, {{WRAPPER}} .slick-next' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'_tab_arrow_hover',
			[
				'label' => __( 'Hover', 'zoom-elementor-widgets' ),
			]
		);

		$this->add_control(
			'arrow_hover_color',
			[
				'label' => __( 'Color', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .slick-prev:hover, {{WRAPPER}} .slick-next:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'arrow_hover_bg_color',
			[
				'label' => __( 'Background Color', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .slick-prev:hover, {{WRAPPER}} .slick-next:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'arrow_hover_border_color',
			[
				'label' => __( 'Border Color', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::COLOR,
				'condition' => [
					'arrow_border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .slick-prev:hover, {{WRAPPER}} .slick-next:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_style_dots',
			[
				'label' => __( 'Navigation :: Dots', 'zoom-elementor-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'dots_nav_position_y',
			[
				'label' => __( 'Vertical Position', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'default' => [
					'unit' => 'px',
					'size' => 10,
				],
				'range' => [
					'px' => [
						'min' => -100,
						'max' => 500,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .slick-dots' => 'bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'dots_nav_spacing',
			[
				'label' => __( 'Spacing', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .slick-dots li' => 'margin-right: calc({{SIZE}}{{UNIT}} / 2); margin-left: calc({{SIZE}}{{UNIT}} / 2);',
				],
			]
		);

		$this->add_responsive_control(
			'dots_nav_align',
			[
				'label' => __( 'Alignment', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'zoom-elementor-widgets' ),
						'icon' => 'fa fa-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'zoom-elementor-widgets' ),
						'icon' => 'fa fa-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'zoom-elementor-widgets' ),
						'icon' => 'fa fa-align-right',
					],
				],
				'toggle' => true,
				'selectors' => [
					'{{WRAPPER}} .slick-dots' => 'text-align: {{VALUE}}'
				]
			]
		);

		$this->start_controls_tabs( '_tabs_dots' );
		$this->start_controls_tab(
			'_tab_dots_normal',
			[
				'label' => __( 'Normal', 'zoom-elementor-widgets' ),
			]
		);

		$this->add_control(
			'dots_nav_size',
			[
				'label' => __( 'Size', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .slick-dots li button:before' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'dots_nav_color',
			[
				'label' => __( 'Color', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .slick-dots li button:before' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'_tab_dots_hover',
			[
				'label' => __( 'Hover', 'zoom-elementor-widgets' ),
			]
		);

		$this->add_control(
			'dots_nav_hover_color',
			[
				'label' => __( 'Color', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .slick-dots li button:hover:before' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'_tab_dots_active',
			[
				'label' => __( 'Active', 'zoom-elementor-widgets' ),
			]
		);

		$this->add_control(
			'dots_nav_active_size',
			[
				'label' => __( 'Size', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .slick-dots li.slick-active button:before' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'dots_nav_active_color',
			[
				'label' => __( 'Color', 'zoom-elementor-widgets' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .slick-dots .slick-active button:before' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Get All Query Arguments from Settings.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param  array  $settings  The settings array that contains the query arguments.
	 * @param  string $post_type The post type that the query will be run for.
	 * @return array             Array of all the query arguments in the given settings array.
	 */
	public function get_query_args( $settings = [], $post_type = 'post' ) {
		$settings = wp_parse_args(
			$settings,
			[
				'posts_type' => $post_type,
				'posts_ids' => [],
				'posts_orderby' => 'date',
				'posts_order' => 'desc',
				'posts_amount' => 5,
				'posts_offset' => 0,
				'posts__not_in' => []
			]
		);

		$args = [
			'orderby' => $settings[ 'posts_orderby' ],
			'order' => $settings[ 'posts_order' ],
			'ignore_sticky_posts' => 1,
			'post_status' => 'publish',
			'posts_per_page' => intval( $settings[ 'posts_amount' ] ),
			'offset' => intval( $settings[ 'posts_offset' ] )
		];

		if ( 'by_id' === $settings[ 'posts_type' ] ) {
			$args[ 'post_type' ] = 'any';
			$args[ 'post__in' ] = empty( $settings[ 'posts_ids' ] ) ? [0] : $settings[ 'posts_ids' ];
		} else {
			$args[ 'post_type' ] = $settings[ 'posts_type' ];
			$args[ 'tax_query' ] = [];

			$taxonomies = get_object_taxonomies( $settings[ 'posts_type' ], 'objects' );

			foreach ( $taxonomies as $object ) {
				$setting_key = 'posts_' . $object->name . '_ids';

				if ( ! empty( $settings[ $setting_key ] ) ) {
					$args[ 'tax_query' ][] = [
						'taxonomy' => $object->name,
						'field' => 'term_id',
						'terms' => $settings[ $setting_key ]
					];
				}
			}

			if ( ! empty( $args[ 'tax_query' ] ) ) {
				$args[ 'tax_query' ][ 'relation' ] = 'AND';
			}
		}

		if ( ! empty( $settings[ 'posts_authors' ] ) ) {
			$args[ 'author__in' ] = $settings[ 'posts_authors' ];
		}

		if ( ! empty( $settings[ 'posts__not_in' ] ) ) {
			$args[ 'post__not_in' ] = $settings[ 'posts__not_in' ];
		}

		return $args;
	}

	/**
	 * Get embed params.
	 *
	 * Retrieve video widget embed parameters.
	 *
	 * @param array $slide The slide to get the data from.
	 * @since 1.0.0
	 * @access public
	 * @return array Video embed parameters.
	 */
	public function get_embed_params( $slide ) {
		$settings = $this->get_settings_for_display();

		$params = [];

		if ( $settings[ 'video_autoplay' ] ) {
			$params[ 'video_autoplay' ] = '1';

			if ( $settings['play_on_mobile'] ) {
				$params[ 'playsinline' ] = '1';
			}
		}

		$params_dictionary = [];

		if ( 'youtube' === $slide[ 'video_type' ] ) {
			$params_dictionary = [
				'video_loop' => 'loop',
				'video_controls' => 'controls',
				'video_mute' => 'mute',
				'video_rel' => 'rel',
				'video_modestbranding' => 'modestbranding'
			];

			if ( $settings[ 'video_loop' ] ) {
				$video_properties = Embed::get_video_properties( $slide[ 'youtube_url' ] );

				$params[ 'playlist' ] = $video_properties[ 'video_id' ];
			}

			$params[ 'wmode' ] = 'opaque';
		} elseif ( 'vimeo' === $slide[ 'video_type' ] ) {
			$params_dictionary = [
				'video_loop' => 'loop',
				'video_mute' => 'muted',
				'vimeo_title' => 'title',
				'vimeo_portrait' => 'portrait',
				'vimeo_byline' => 'byline',
			];

			$params[ 'color' ] = str_replace( '#', '', $settings[ 'video_controls_color' ] );
			$params[ 'autopause' ] = '0';
		} elseif ( 'dailymotion' === $slide[ 'video_type' ] ) {
			$params_dictionary = [
				'video_controls' => 'controls',
				'video_mute' => 'mute',
				'video_showinfo' => 'ui-start-screen-info',
				'video_logo' => 'ui-logo',
			];

			$params[ 'ui-highlight' ] = str_replace( '#', '', $settings[ 'video_controls_color' ] );

			$params[ 'endscreen-enable' ] = '0';
		}

		foreach ( $params_dictionary as $key => $param_name ) {
			$setting_name = $param_name;

			if ( is_string( $key ) ) {
				$setting_name = $key;
			}

			$setting_value = $settings[ $setting_name ] ? '1' : '0';

			$params[ $param_name ] = $setting_value;
		}

		return $params;
	}

	/**
	 * Get video embed options.
	 * 
	 * @param array $slide The slide to get the data from.
	 * @since 1.0.0
	 * @access private
	 * @return array
	 */
	private function get_embed_options( $slide ) {
		$embed_options = [];
		$settings = $this->get_settings_for_display();

		if ( 'youtube' === $slide[ 'video_type' ] ) {
			$embed_options[ 'privacy' ] = $settings[ 'yt_privacy' ];
		}

		return $embed_options;
	}

	/**
	 * Get hosted video parameters.
	 *
	 * @since 1.0.0
	 * @access private
	 * @return array
	 */
	private function get_hosted_params() {
		$video_params = [];
		$settings = $this->get_settings_for_display();

		foreach ( [ 'video_autoplay', 'video_loop', 'video_controls' ] as $option_name ) {
			if ( $settings[ $option_name ] ) {
				$video_params[ $option_name ] = '';
			}
		}

		if ( $settings[ 'video_mute' ] ) {
			$video_params[ 'muted' ] = 'muted';
		}

		if ( $settings[ 'play_on_mobile' ] ) {
			$video_params[ 'playsinline' ] = '';
		}

		if ( ! $settings[ 'video_download_button' ] ) {
			$video_params[ 'controlsList' ] = 'nodownload';
		}

		if ( $settings[ 'video_poster' ][ 'url' ] ) {
			$video_params[ 'poster' ] = $settings[ 'video_poster' ][ 'url' ];
		}

		return $video_params;
	}

	/**
	 * Get the URL of a hosted video.
	 *
	 * @param array $slide The slide to get the data from.
	 * @since 1.0.0
	 * @access private
	 * @return string
	 */
	private function get_hosted_video_url( $slide ) {
		if ( ! empty( $slide[ 'insert_url' ] ) ) {
			$video_url = $slide[ 'external_url' ][ 'url' ];
		} else {
			$video_url = $slide[ 'hosted_url' ][ 'url' ];
		}

		if ( empty( $video_url ) ) {
			return '';
		}

		return $video_url;
	}

	/**
	 * Render a hosted video.
	 *
	 * @param array $slide The slide to get the data from.
	 * @since 1.0.0
	 * @access private
	 * @return void
	 */
	private function render_hosted_video( $slide ) {
		$video_url = $this->get_hosted_video_url( $slide );

		if ( empty( $video_url ) ) {
			return;
		}

		$video_params = $this->get_hosted_params();

		?>
		<video class="elementor-video" src="<?php echo esc_url( $video_url ); ?>" <?php echo Utils::render_html_attributes( $video_params ); ?>></video>
		<?php
	}

	/**
	 * Render the Widget.
	 *
	 * Renders the widget on the frontend.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$slides = [];

		if ( 'posts' == $settings[ 'slides_source' ] ) {
			$args = $this->get_query_args( $settings );
			$query = new \WP_Query( $args );

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();

					$slides[] = [
						'_id' => get_the_ID(),
						'title' => get_the_title(),
						'subtitle' => get_the_excerpt(),
						'image' => [ 'id' => get_post_thumbnail_id(), 'url' => false ],
						'link' => [ 'url' => get_permalink() ]
					];
				}

				wp_reset_postdata();
			}
		} else {
			$slides = $settings[ 'slides' ];
		}

		if ( empty( $slides ) ) {
			return;
		}

		?><div class="zewjs-slick zew-slick zew-slick--slider">

			<?php foreach ( $slides as $slide ) :
				if ( isset( $slide[ 'video_type' ] ) && ! empty( $slide[ 'video_type' ] ) ) {
					$video_url = $slide[ $slide[ 'video_type' ] . '_url' ];
					$video_html = '';

					if ( 'hosted' === $slide[ 'video_type' ] ) {
						$video_url = $this->get_hosted_video_url( $slide );
					} else {
						$embed_params = $this->get_embed_params( $slide );
						$embed_options = $this->get_embed_options( $slide );
					}

					if ( ! empty( $video_url ) ) {
						if ( 'youtube' === $slide[ 'video_type' ] ) {
							$video_html = '<div class="elementor-video"></div>';
						}

						if ( 'hosted' === $slide[ 'video_type' ] ) {
							ob_start();

							$this->render_hosted_video( $slide );

							$video_html = ob_get_clean();
						} else {
							$is_static_render_mode = Plugin::$instance->frontend->is_static_render_mode();
							$post_id = get_queried_object_id();

							if ( $is_static_render_mode ) {
								$video_html = Embed::get_embed_thumbnail_html( $video_url, $post_id );
							// YouTube API requires a different markup which was set above.
							} else if ( 'youtube' !== $slide[ 'video_type' ] ) {
								$video_html = Embed::get_embed_html( $video_url, $embed_params, $embed_options );
							}
						}

						if ( empty( $video_html ) ) {
							$video_html = esc_url( $video_url );
						}

						$this->add_render_attribute( 'video-wrapper', 'class', 'elementor-wrapper' );
						$this->add_render_attribute( 'video-wrapper', 'class', 'zew-video-wrapper' );
						$this->add_render_attribute( 'video-wrapper', 'class', 'e-' . $slide[ 'video_type' ] . '-video' );
						$this->add_render_attribute( 'video-wrapper', 'data-video-type', $slide[ 'video_type' ] );
						$this->add_render_attribute( 'video-wrapper', 'data-video-url', $video_url );
					}
				}

				$image = wp_get_attachment_image_url( $slide[ 'image' ][ 'id' ], $settings[ 'thumbnail_size' ] );

				if ( ! $image ) {
					$image = $slide[ 'image' ][ 'url' ];
				}

				$item_tag = 'div';
				$id = 'zew-slick-item-' . $slide ['_id' ];

				$this->add_render_attribute( $id, 'class', 'zew-slick-item' );

				if ( isset( $slide[ 'link' ] ) && ! empty( $slide[ 'link' ][ 'url' ] ) ) {
					$item_tag = 'a';
					$this->add_link_attributes( $id, $slide[ 'link' ] );
				}
				?>

				<div class="zew-slick-slide">

					<<?php echo $item_tag; ?> <?php $this->print_render_attribute_string( $id ); ?>>

						<?php if ( $image ) : ?>
							<img class="zew-slick-img" src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $slide[ 'title' ] ); ?>">
						<?php endif; ?>

						<?php if ( isset( $slide[ 'video_type' ] ) && ! empty( $slide[ 'video_type' ] ) && ! empty( $video_html ) ) : ?>
							<div <?php echo $this->get_render_attribute_string( 'video-wrapper' ); ?>>
								<?php echo $video_html; ?>

								<?php if ( 'yes' === $settings[ 'show_play_icon' ] ) : ?>
									<div class="elementor-custom-embed-play" role="button">
										<i class="eicon-play" aria-hidden="true"></i>
										<span class="elementor-screen-only"><?php _e( 'Play Video', 'zoom-elementor-widgets' ); ?></span>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ( $slide[ 'title' ] || $slide[ 'subtitle' ] ) : ?>
							<div class="zew-slick-content">
								<?php if ( $slide[ 'title' ] ) : ?>
									<h2 class="zew-slick-title"><?php echo ZOOM_Elementor_Widgets::custom_kses( $slide[ 'title' ] ); ?></h2>
								<?php endif; ?>
								<?php if ( $slide[ 'subtitle' ] ) : ?>
									<p class="zew-slick-subtitle"><?php echo ZOOM_Elementor_Widgets::custom_kses( $slide[ 'subtitle' ] ); ?></p>
								<?php endif; ?>
							</div>
						<?php endif; ?>

					</<?php echo $item_tag; ?>>

				</div>

			<?php endforeach; ?>

		</div>

		<?php if ( ! empty( $settings[ 'arrow_prev_icon' ][ 'value' ] ) ) : ?>
			<button type="button" class="slick-prev"><?php Icons_Manager::render_icon( $settings[ 'arrow_prev_icon' ], [ 'aria-hidden' => 'true' ] ); ?></button>
		<?php endif; ?>

		<?php if ( ! empty( $settings[ 'arrow_next_icon' ][ 'value' ] ) ) : ?>
			<button type="button" class="slick-next"><?php Icons_Manager::render_icon( $settings[ 'arrow_next_icon' ], [ 'aria-hidden' => 'true' ] ); ?></button>
		<?php endif; ?>

		<?php
	}
}
