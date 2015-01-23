{if is_set( $sensor )|not()}{def $sensor = sensor_root_handler()}{/if}
{*
{ezscript_require(array('ezjsc::jquery','jquery.flexslider-min.js','mainflexislider.js'))}
{ezcss_require('Flexslider/flexslider.css')}
*}

{if or( $sensor.forum_container_node.children_count|gt(1), is_set( $view_parameters.more ))}

  <section id="slider_wrapper" class="slider_wrapper full_page_photo">
    <div id="main_flexslider" class="flexslider">
      <ul class="slides list-unstyled">
        <li class="item" style="background-image: url({$sensor.forum_container_node.data_map.image.content.original.full_path|ezroot(no)})">
          <div class="container">
            <div class="carousel-caption">
              <h1>{attribute_view_gui attribute=$sensor.forum_container_node.data_map.title}</h1>
              <p class="lead skincolored">{attribute_view_gui attribute=$sensor.forum_container_node.data_map.subtitle}</p>
          </div>
        </li>
      </ul>
    </div>
  </section>

{else}

  {def $first_child = $sensor.forum_container_node.children[0]}
  <section id="slider_wrapper" class="slider_wrapper full_page_photo">
    <div id="main_flexslider" class="flexslider">
      <ul class="slides list-unstyled">
        <li class="item" style="background-image: url({$first_child.data_map.image.content.original.full_path|ezroot(no)})">
          <div class="container">
            <div class="carousel-caption">
              <h1>{$first_child.name|wash()|bracket_to_strong}</h1>
            </div>
        </li>
      </ul>
    </div>
  </section>

{/if}