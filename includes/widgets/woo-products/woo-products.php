<?php
namespace WPZOOMElementorWidgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Widget_Base;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * ZOOM Elementor Widgets - Woo Products Widget.
 *
 * Elementor widget that inserts a customizable list of WooCommerce products.
 *
 * @since 1.0.0
 */
class Woo_Products extends Widget_Base {
	/**
	 * @var \WP_Query
	 */
	private $query = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );

		wp_register_style( 'wpzoom-elementor-addons-css-frontend-woo-products', plugins_url( 'frontend.css', __FILE__ ), [], WPZOOM_EL_ADDONS_VER );
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
		return 'wpzoom-elementor-addons-woo-products';
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
		return __( 'Woo Products', 'wpzoom-elementor-addons' );
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
		return 'eicon-woocommerce';
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
		return [ 'wpzoom-elementor-addons' ];
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
			'wpzoom-elementor-addons-css-frontend-woo-products'
		];
	}

	/**
	 * Get the query
	 *
	 * Returns the current query.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return \WP_Query The current query.
	 */
	public function get_query() {
		return $this->query;
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
		$this->start_controls_section(
				'section_woo_products',
				[
					'label' => __( 'Products', 'wpzoom-elementor-addons' ),
				]
		);

		$this->add_control(
				'columns',
				[
					'label' => __( 'Columns', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::SELECT,
					'default' => '4',
					'options' => [
						'1' => '1',
						'2' => '2',
						'3' => '3',
						'4' => '4',
						'5' => '5',
						'6' => '6',
						'7' => '7',
						'8' => '8',
						'9' => '9',
						'10' => '10',
					],
				]
		);

		$this->add_control(
				'posts_per_page',
				[
					'label' => __( 'Products Count', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::NUMBER,
					'default' => '4',
				]
		);

		$this->add_control(
				'pagination',
				[
					'label' => __( 'Pagination', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::SWITCHER,
					'default' => '',
				]
		);

		$this->add_control(
				'pagination_position',
				[
					'label' => __( 'Pagination Position', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::CHOOSE,
					'options' => [
						'left' => [
							'title' => __( 'Left', 'wpzoom-elementor-addons' ),
							'icon' => 'fa fa-align-left',
						],
						'center' => [
							'title' => __( 'Center', 'wpzoom-elementor-addons' ),
							'icon' => 'fa fa-align-center',
						],
						'right' => [
							'title' => __( 'Right', 'wpzoom-elementor-addons' ),
							'icon' => 'fa fa-align-right',
						],
					],
					'selectors' => [
						'{{WRAPPER}} ul.page-numbers' => 'text-align: {{VALUE}};',
					],
					'default' => 'center',
					'condition' => [
						'pagination' => 'yes',
					],
				]
		);

		$this->end_controls_section();

		$this->start_controls_section(
				'section_filter',
				[
					'label' => __( 'Query', 'wpzoom-elementor-addons' ),
					'tab' => Controls_Manager::TAB_CONTENT,
				]
		);

		$this->add_control(
				'query_type',
				[
					'label' => __( 'Source', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::SELECT,
					'default' => 'all',
					'options' => [
						'all' => __( 'All Products', 'wpzoom-elementor-addons' ),
						'custom' => __( 'Custom Query', 'wpzoom-elementor-addons' ),
						'manual' => __( 'Manual Selection', 'wpzoom-elementor-addons' ),
					],
				]
		);

		$this->add_control(
				'category_filter_rule',
				[
					'label' => __( 'Cat Filter Rule', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::SELECT,
					'default' => 'IN',
					'options' => [
						'IN' => __( 'Match Categories', 'wpzoom-elementor-addons' ),
						'NOT IN' => __( 'Exclude Categories', 'wpzoom-elementor-addons' ),
					],
					'condition' => [
						'query_type' => 'custom',
					],
				]
		);

		$this->add_control(
				'category_filter',
				[
					'label' => __( 'Select Categories', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::SELECT2,
					'multiple' => true,
					'default' => '',
					'options' => $this->get_product_categories(),
					'condition' => [
						'query_type' => 'custom',
					],
				]
		);

		$this->add_control(
				'tag_filter_rule',
				[
					'label' => __( 'Tag Filter Rule', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::SELECT,
					'default' => 'IN',
					'options' => [
						'IN' => __( 'Match Tags', 'wpzoom-elementor-addons' ),
						'NOT IN' => __( 'Exclude Tags', 'wpzoom-elementor-addons' ),
					],
					'condition' => [
						'query_type' => 'custom',
					],
				]
		);

		$this->add_control(
				'tag_filter',
				[
					'label' => __( 'Select Tags', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::SELECT2,
					'multiple' => true,
					'default' => '',
					'options' => $this->get_product_tags(),
					'condition' => [
						'query_type' => 'custom',
					],
				]
		);

		$this->add_control(
				'offset',
				[
					'label' => __( 'Offset', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::NUMBER,
					'default' => 0,
					'description' => __( 'Number of post to displace or pass over.', 'wpzoom-elementor-addons' ),
					'condition' => [
						'query_type' => 'custom',
					],
				]
		);

		/*$this->add_control(
				'query_manual_ids',
				[
					'label' => __( 'Select Products', 'wpzoom-elementor-addons' ),
					'type' => 'etww-query-posts',
					'post_type' => 'product',
					'multiple' => true,
					'condition' => [
						'query_type' => 'manual',
					],
				]
		);*/

		$this->add_control(
				'query_exclude',
				[
					'label' => __( 'Exclude', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => [
						'query_type!' => 'manual',
					],
				]
		);

		/*$this->add_control(
				'query_exclude_ids',
				[
					'label' => __( 'Select Products', 'wpzoom-elementor-addons' ),
					'type' => 'etww-query-posts',
					'post_type' => 'product',
					'multiple' => true,
					'description' => __( 'Select products to exclude from the query.', 'wpzoom-elementor-addons' ),
					'condition' => [
						'query_type!' => 'manual',
					],
				]
		);*/

		$this->add_control(
				'query_exclude_current',
				[
					'label' => __( 'Exclude Current Product', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::SWITCHER,
					'label_on' => __( 'Yes', 'wpzoom-elementor-addons' ),
					'label_off' => __( 'No', 'wpzoom-elementor-addons' ),
					'return_value' => 'yes',
					'default' => '',
					'description' => __( 'Enable this option to remove current product from the query.', 'wpzoom-elementor-addons' ),
					'condition' => [
						'query_type!' => 'manual',
					],
				]
		);

		$this->add_control(
				'advanced',
				[
					'label' => __( 'Advanced', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::HEADING,
				]
		);

		$this->add_control(
				'filter_by',
				[
					'label' => __( 'Filter By', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						'' => __( 'None', 'wpzoom-elementor-addons' ),
						'featured' => __( 'Featured', 'wpzoom-elementor-addons' ),
						'sale' => __( 'Sale', 'wpzoom-elementor-addons' ),
					],
				]
		);

		$this->add_control(
				'orderby',
				[
					'label' => __( 'Order by', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::SELECT,
					'default' => 'date',
					'options' => [
						'date' => __( 'Date', 'wpzoom-elementor-addons' ),
						'title' => __( 'Title', 'wpzoom-elementor-addons' ),
						'price' => __( 'Price', 'wpzoom-elementor-addons' ),
						'popularity' => __( 'Popularity', 'wpzoom-elementor-addons' ),
						'rating' => __( 'Rating', 'wpzoom-elementor-addons' ),
						'rand' => __( 'Random', 'wpzoom-elementor-addons' ),
						'menu_order' => __( 'Menu Order', 'wpzoom-elementor-addons' ),
					],
				]
		);

		$this->add_control(
				'order',
				[
					'label' => __( 'Order', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::SELECT,
					'default' => 'desc',
					'options' => [
						'asc' => __( 'ASC', 'wpzoom-elementor-addons' ),
						'desc' => __( 'DESC', 'wpzoom-elementor-addons' ),
					],
				]
		);

		$this->end_controls_section();

		$this->start_controls_section(
				'section_item_style',
				[
					'label' => __( 'Item', 'wpzoom-elementor-addons' ),
					'tab' => Controls_Manager::TAB_STYLE,
				]
		);

		$this->add_control(
				'item_background_color',
				[
					'label' => __( 'Background Color', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product' => 'background-color: {{VALUE}};',
					],
				]
		);

		$this->add_group_control(
				Group_Control_Border::get_type(),
				[
					'name' => 'item_border',
					'placeholder' => '1px',
					'selector' => '{{WRAPPER}} .woocommerce ul.products li.product',
					'separator' => 'before',
				]
		);

		$this->add_control(
				'item_border_radius',
				[
					'label' => __( 'Border Radius', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
		);

		$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'item_box_shadow',
					'selector' => '{{WRAPPER}} .woocommerce ul.products li.product',
				]
		);

		$this->add_responsive_control(
				'item_padding',
				[
					'label' => __( 'Padding', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'separator' => 'before',
				]
		);
		$this->add_responsive_control(
				'item_margin',
				[
					'label' => __( 'Margin', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
		);


		$this->end_controls_section();

		$this->start_controls_section(
				'section_image_style',
				[
					'label' => __( 'Image', 'wpzoom-elementor-addons' ),
					'tab' => Controls_Manager::TAB_STYLE,
				]
		);

		$this->add_group_control(
				Group_Control_Border::get_type(),
				[
					'name' => 'image_border',
					'placeholder' => '1px',
					'selector' => '{{WRAPPER}} .woocommerce ul.products li.product img:not(.secondary-image)',
				]
		);

		$this->add_control(
				'image_border_radius',
				[
					'label' => __( 'Border Radius', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => ['px', '%'],
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product img:not(.secondary-image), {{WRAPPER}} .woocommerce ul.products li.product .woo-entry-inner li.image-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; position: relative; overflow: hidden;',
					],
				]
		);

		$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'image_box_shadow',
					'selector' => '{{WRAPPER}} .woocommerce ul.products li.product img:not(.secondary-image)',
				]
		);

		$this->add_responsive_control(
				'image_margin',
				[
					'label' => __( 'Padding', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product img:not(.secondary-image)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
		);

		$this->end_controls_section();

		$this->start_controls_section(
				'section_content_style',
				[
					'label' => __( 'Content', 'wpzoom-elementor-addons' ),
					'tab' => Controls_Manager::TAB_STYLE,
				]
		);

		$this->add_control(
				'category_heading',
				[
					'label' => __( 'Category', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::HEADING,
				]
		);

		$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name' => 'category_typography',
					'scheme' => Typography::TYPOGRAPHY_4,
					'selector' => '{{WRAPPER}} .woocommerce ul.products li.product li.category a, {{WRAPPER}} .woocommerce ul.products li.product .archive-product-categories a',
				]
		);

		$this->add_control(
				'category_color',
				[
					'label' => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product li.category a, {{WRAPPER}} .woocommerce ul.products li.product .archive-product-categories a' => 'color: {{VALUE}};',
					],
				]
		);

		$this->add_control(
				'category_hover_color',
				[
					'label' => esc_html__( 'Hover Color', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product li.category a:hover, {{WRAPPER}} .woocommerce ul.products li.product .archive-product-categories a:hover' => 'color: {{VALUE}};',
					],
				]
		);

		$this->add_responsive_control(
				'category_margin',
				[
					'label' => __( 'Margin', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product li.category, {{WRAPPER}} .woocommerce ul.products li.product .archive-product-categories a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
		);

		$this->add_control(
				'title_heading',
				[
					'label' => __( 'Title', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::HEADING,
					'separator' => 'before',
				]
		);

		$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name' => 'title_typography',
					'scheme' => Typography::TYPOGRAPHY_4,
					'selector' => '{{WRAPPER}} .woocommerce ul.products li.product .woocommerce-loop-product__title',
				]
		);

		$this->add_control(
				'title_color',
				[
					'label' => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product .woocommerce-loop-product__title' => 'color: {{VALUE}};',
					],
				]
		);

		$this->add_control(
				'title_hover_color',
				[
					'label' => esc_html__( 'Hover Color', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product .woocommerce-loop-product__title:hover' => 'color: {{VALUE}};',
					],
				]
		);

		$this->add_responsive_control(
				'title_margin',
				[
					'label' => __( 'Margin', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product .woocommerce-loop-product__title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
		);

		$this->add_control(
				'price_heading',
				[
					'label' => __( 'Price', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::HEADING,
					'separator' => 'before',
				]
		);

		$this->add_control(
				'price_color',
				[
					'label' => esc_html__( 'Price Color', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product .price, {{WRAPPER}} .woocommerce ul.products li.product .price .amount' => 'color: {{VALUE}};',
					],
				]
		);

		$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name' => 'price_typography',
					'scheme' => Typography::TYPOGRAPHY_4,
					'selector' => '{{WRAPPER}} .woocommerce ul.products li.product .price, {{WRAPPER}} .woocommerce ul.products li.product .price .amount',
				]
		);

		$this->add_control(
				'del_price_color',
				[
					'label' => esc_html__( 'Del Price Color', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::COLOR,
					'separator' => 'before',
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product .price del .amount' => 'color: {{VALUE}};',
					],
				]
		);

		$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name' => 'del_price_typography',
					'scheme' => Typography::TYPOGRAPHY_4,
					'selector' => '{{WRAPPER}} .woocommerce ul.products li.product .price del .amount',
				]
		);

		$this->add_responsive_control(
				'price_margin',
				[
					'label' => __( 'Margin', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product .price' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
		);

		$this->add_control(
				'rating_heading',
				[
					'label' => __( 'Rating', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::HEADING,
					'separator' => 'before',
				]
		);

		$this->add_control(
				'rating_color',
				[
					'label' => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product .star-rating span::before' => 'color: {{VALUE}};',
					],
				]
		);

		$this->add_control(
				'rating_fill_color',
				[
					'label' => esc_html__( 'Fill Color', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product .star-rating::before' => 'color: {{VALUE}};',
					],
				]
		);

		$this->end_controls_section();

		$this->start_controls_section(
				'section_button_style',
				[
					'label' => __( 'Button', 'wpzoom-elementor-addons' ),
					'tab' => Controls_Manager::TAB_STYLE,
				]
		);

		$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name' => 'button_typography',
					'scheme' => Typography::TYPOGRAPHY_4,
					'selector' => '{{WRAPPER}} .woocommerce ul.products li.product .button',
				]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
				'tab_button_normal',
				[
					'label' => __( 'Normal', 'wpzoom-elementor-addons' ),
				]
		);

		$this->add_control(
				'button_background_color',
				[
					'label' => __( 'Background Color', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product .button' => 'background-color: {{VALUE}};',
					],
				]
		);

		$this->add_control(
				'button_text_color',
				[
					'label' => __( 'Text Color', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product .button' => 'color: {{VALUE}};',
					],
				]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
				'tab_button_hover',
				[
					'label' => __( 'Hover', 'wpzoom-elementor-addons' ),
				]
		);

		$this->add_control(
				'button_hover_background_color',
				[
					'label' => __( 'Background Color', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product .button:hover' => 'background-color: {{VALUE}};',
					],
				]
		);

		$this->add_control(
				'button_hover_color',
				[
					'label' => __( 'Text Color', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product .button:hover' => 'color: {{VALUE}};',
					],
				]
		);

		$this->add_control(
				'button_hover_border_color',
				[
					'label' => __( 'Border Color', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product .button:hover' => 'border-color: {{VALUE}};',
					],
				]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
				Group_Control_Border::get_type(),
				[
					'name' => 'button_border',
					'placeholder' => '1px',
					'default' => '1px',
					'selector' => '{{WRAPPER}} .woocommerce ul.products li.product .button',
					'separator' => 'before',
				]
		);

		$this->add_control(
				'button_border_radius',
				[
					'label' => __( 'Border Radius', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product .button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
		);

		$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'button_box_shadow',
					'selector' => '{{WRAPPER}} .woocommerce ul.products li.product .button',
				]
		);

		$this->add_responsive_control(
				'button_padding',
				[
					'label' => __( 'Padding', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product .button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'separator' => 'before',
				]
		);

		$this->add_responsive_control(
				'button_margin',
				[
					'label' => __( 'Margin', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product .button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
		);

		$this->end_controls_section();

		$this->start_controls_section(
				'section_badge_style',
				[
					'label' => __( 'Badge', 'wpzoom-elementor-addons' ),
					'tab' => Controls_Manager::TAB_STYLE,
				]
		);

		$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name' => 'badge_typography',
					'scheme' => Typography::TYPOGRAPHY_4,
					'selector' => '{{WRAPPER}} .woocommerce span.onsale',
				]
		);

		$this->add_control(
				'badge_background_color',
				[
					'label' => __( 'Background Color', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce span.onsale' => 'background-color: {{VALUE}};',
					],
				]
		);

		$this->add_control(
				'badge_color',
				[
					'label' => __( 'Color', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce span.onsale' => 'color: {{VALUE}};',
					],
				]
		);

		$this->add_group_control(
				Group_Control_Border::get_type(),
				[
					'name' => 'badge_border',
					'placeholder' => '1px',
					'selector' => '{{WRAPPER}} .woocommerce span.onsale',
					'separator' => 'before',
				]
		);

		$this->add_control(
				'badge_border_radius',
				[
					'label' => __( 'Border Radius', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						'{{WRAPPER}} .woocommerce span.onsale' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
		);

		$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'badge_box_shadow',
					'selector' => '{{WRAPPER}} .woocommerce span.onsale',
				]
		);

		$this->add_responsive_control(
				'badge_padding',
				[
					'label' => __( 'Padding', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .woocommerce span.onsale' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'separator' => 'before',
				]
		);

		$this->add_responsive_control(
				'badge_margin',
				[
					'label' => __( 'Margin', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .woocommerce span.onsale' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
		);

		$this->end_controls_section();
	}

	/**
	 * Get product categories.
	 *
	 * Retrieve a list of all product categories.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array All product categories.
	 */
	protected function get_product_categories() {
		$product_cat = array();

		$cat_args = array(
			'orderby' => 'name',
			'order' => 'asc',
			'hide_empty' => false,
		);

		$product_categories = get_terms( 'product_cat', $cat_args );

		if ( !empty( $product_categories ) ) {
			foreach ( $product_categories as $key => $category ) {
				if ( is_object( $category ) && property_exists( $category, 'slug' ) && property_exists( $category, 'name' ) ) {
					$product_cat[ $category->slug ] = $category->name;
				}
			}
		}

		return $product_cat;
	}

	/**
	 * Get product tags.
	 *
	 * Retrieve a list of all product tags.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array All product tags.
	 */
	protected function get_product_tags() {
		$product_tag = array();

		$tag_args = array(
			'orderby' => 'name',
			'order' => 'asc',
			'hide_empty' => false,
		);

		$product_tags = get_terms( 'product_tag', $tag_args );

		if ( ! empty( $product_tags ) && ! is_wp_error( $product_tags ) ) {
			foreach ( $product_tags as $key => $tag ) {
				$product_tag[ $tag->slug ] = $tag->name;
			}
		}

		return $product_tag;
	}

	/**
	 * Get pagination markup.
	 *
	 * Retrieve the pagination markup.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void|string
	 */
	function pagination( $query = '', $echo = true ) {
		// Arrows with RTL support
		$prev_arrow = is_rtl() ? 'fas fa-angle-right' : 'fas fa-angle-left';
		$next_arrow = is_rtl() ? 'fas fa-angle-left' : 'fas fa-angle-right';

		// Get global $query
		if (!$query) {
			global $wp_query;
			$query = $wp_query;
		}

		// Set vars
		$total = $query->max_num_pages;
		$big = 999999999;

		// Display pagination if total var is greater then 1 (current query is paginated)
		if ( $total > 1 ) {
			// Get current page
			if ( $current_page = get_query_var( 'paged' ) ) {
				$current_page = $current_page;
			} elseif ( $current_page = get_query_var( 'page' ) ) {
				$current_page = $current_page;
			} else {
				$current_page = 1;
			}

			// Get permalink structure
			if ( get_option( 'permalink_structure' ) ) {
				if ( is_page() ) {
					$format = 'page/%#%/';
				} else {
					$format = '/%#%/';
				}
			} else {
				$format = '&paged=%#%';
			}

			$args = apply_filters( 'wpz_pagination_args', array(
				'base' => str_replace( $big, '%#%', html_entity_decode( get_pagenum_link( $big ) ) ),
				'format' => $format,
				'current' => max( 1, $current_page ),
				'total' => $total,
				'mid_size' => 3,
				'type' => 'list',
				'prev_text' => '<i class="' . $prev_arrow . '"></i>',
				'next_text' => '<i class="' . $next_arrow . '"></i>',
			) );

			// Output pagination
			if ( $echo ) {
				echo '<div class="wpz-pagination clr">' . wp_kses_post( paginate_links( $args ) ) . '</div>';
			} else {
				return '<div class="wpz-pagination clr">' . wp_kses_post( paginate_links( $args ) ) . '</div>';
			}
		}
	}

	/**
	 * Query all products.
	 *
	 * Queries the product database using the given arguments.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function query_posts() {
		if ( ! function_exists( 'WC' ) ) {
			$this->query = new \WP_Query();
			return;
		}

		$settings = $this->get_settings();

		global $post;

		$query_args = [
			'post_type' => 'product',
			'posts_per_page' => $settings[ 'posts_per_page' ],
			'post__not_in' => array(),
		];

		// Default ordering args.
		$ordering_args = WC()->query->get_catalog_ordering_args( $settings[ 'orderby' ], $settings[ 'order' ] );

		$query_args[ 'orderby' ] = $ordering_args[ 'orderby' ];
		$query_args[ 'order' ] = $ordering_args[ 'order' ];

		if ( 'sale' === $settings[ 'filter_by' ] ) {
			$query_args[ 'post__in' ] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
		} elseif ( 'featured' === $settings[ 'filter_by' ] ) {
			$product_visibility_term_ids = wc_get_product_visibility_term_ids();

			$query_args[ 'tax_query' ][] = [
				'taxonomy' => 'product_visibility',
				'field' => 'term_taxonomy_id',
				'terms' => $product_visibility_term_ids[ 'featured' ],
			];
		}

		if ( 'custom' === $settings[ 'query_type' ] ) {
			if ( !empty( $settings[ 'category_filter' ] ) ) {
				$cat_operator = $settings[ 'category_filter_rule' ];

				$query_args[ 'tax_query' ][] = [
					'taxonomy' => 'product_cat',
					'field' => 'slug',
					'terms' => $settings[ 'category_filter' ],
					'operator' => $cat_operator,
				];
			}

			if ( !empty( $settings[ 'tag_filter' ] ) ) {
				$tag_operator = $settings[ 'tag_filter_rule' ];

				$query_args[ 'tax_query' ][] = [
					'taxonomy' => 'product_tag',
					'field' => 'slug',
					'terms' => $settings[ 'tag_filter' ],
					'operator' => $tag_operator,
				];
			}

			if ( 0 < $settings[ 'offset' ] ) {
				$query_args[ 'offset_to_fix' ] = $settings[ 'offset' ];
			}
		}

		/*if ( 'manual' === $settings[ 'query_type' ] ) {
			$manual_ids = $settings[ 'query_manual_ids' ];
			$query_args[ 'post__in' ] = $manual_ids;
		}*/

		if ( 'manual' !== $settings[ 'query_type' ] ) {
			/*if ( '' !== $settings[ 'query_exclude_ids' ] ) {
				$exclude_ids = $settings[ 'query_exclude_ids' ];
				$query_args[ 'post__not_in' ] = $exclude_ids;
			}*/

			if ( 'yes' === $settings[ 'query_exclude_current' ] ) {
				$query_args[ 'post__not_in' ][] = $post->ID;
			}
		}

		if ( 'yes' === $settings[ 'pagination' ] ) {
			// Paged
			global $paged;
			if ( get_query_var( 'paged' ) ) {
				$paged = get_query_var( 'paged' );
			} else if ( get_query_var( 'page' ) ) {
				$paged = get_query_var( 'page' );
			} else {
				$paged = 1;
			}

			$query_args[ 'posts_per_page' ] = $settings[ 'posts_per_page' ];

			if ( 1 < $paged ) {
				$query_args[ 'paged' ] = $paged;
			}
		}

		$this->query = new \WP_Query( $query_args );
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
	public function render() {
		if ( ! function_exists( 'WC' ) ) {
			printf(
				'<div class="woocommerce error"><h2>%1$s</h2><p>%2$s</p></div>',
				__( 'Error!', 'wpzoom-elementor-addons' ),
				__( 'The WooCommerce plugin could not be found. Please install/activate it in order to use this feature.', 'wpzoom-elementor-addons' )
			);

			return;
		}

		$settings = $this->get_settings();

		$this->query_posts();

		$query = $this->get_query();

		if ( !$query->have_posts() ) {
			return;
		}

		global $woocommerce_loop;

		$woocommerce_loop[ 'columns' ] = (int) $settings[ 'columns' ];

		echo '<div class="woocommerce columns-' . $woocommerce_loop[ 'columns' ] . '">';

		woocommerce_product_loop_start();

		while ( $query->have_posts() ) : $query->the_post();
			wc_get_template_part( 'content', 'product' );
		endwhile;

		woocommerce_product_loop_end();

		// Display pagination if enabled
		if ( 'yes' == $settings[ 'pagination' ] ) {
			$this->pagination( $query );
		}

		woocommerce_reset_loop();

		wp_reset_postdata();

		echo '</div>';
	}
}