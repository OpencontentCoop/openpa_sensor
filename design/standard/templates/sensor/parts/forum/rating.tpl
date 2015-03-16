{def $rating = $attribute.content}

{if eq($attribute.contentclass_attribute_identifier, 'like_rating')}
  {if $attribute.data_int|not()}
    <div class="hreview-aggregate like_rating well well-sm">
      <ul id="ezsr_rating_{$attribute.id}" class="ezsr-star-rating">
        <li id="ezsr_rating_percent_{$attribute.id}" class="ezsr-current-rating" style="width:{$rating.rounded_average|div(1)|mul(100)}%;">{'Currently %current_rating out of 5 Stars.'|i18n('extension/ezstarrating/datatype', '', hash( '%current_rating', concat('<span>', $rating.rounded_average|wash, '</span>') ))}</li>
        {for 1 to 1 as $num}
          <li><a href="JavaScript:void(0);" id="ezsr_{$attribute.id}_{$attribute.version}_{$num}" class="ezsr-stars-{$num}" rel="nofollow" onfocus="this.blur();">{$num}</a></li>
        {/for}
      </ul>
      <span id="ezsr_total_{$attribute.id}">{$rating.rating_count|wash}</span>
      {*<p id="ezsr_just_rated_{$attribute.id}" class="ezsr-just-rated hide">{'Thank you for rating!'|i18n('extension/ezstarrating/datatype', 'When rating')}</p>
      <p id="ezsr_has_rated_{$attribute.id}" class="ezsr-has-rated hide">Hai già votato!</p>*}
    </div>
  {/if}
{else}
  {if $attribute.data_int|not()}
    <div class="hreview-aggregate">

      {if eq($attribute.contentclass_attribute_identifier, 'star_rating')}
        <span class="ezsr-star-rating-label">Poco chiara</span>
      {elseif eq($attribute.contentclass_attribute_identifier, 'usefull_rating')}
        <span class="ezsr-star-rating-label">Poco utile</span>
      {else}
        <span class="ezsr-star-rating-label">Poco</span>
      {/if}

      <ul id="ezsr_rating_{$attribute.id}" class="ezsr-star-rating">
        <li id="ezsr_rating_percent_{$attribute.id}" class="ezsr-current-rating" style="width:{$rating.rounded_average|div(4)|mul(100)}%;">Attualmente <span>{$rating.rounded_average|wash}</span> su 4</li>
        {for 1 to 4 as $num}
          <li><a href="JavaScript:void(0);" id="ezsr_{$attribute.id}_{$attribute.version}_{$num}" title="{$num}" class="ezsr-stars-{$num}" rel="nofollow" onfocus="this.blur();">{$num}</a></li>
        {/for}
      </ul>

      {if eq($attribute.contentclass_attribute_identifier, 'star_rating')}
        <span class="ezsr-star-rating-label">Molto chiara</span>
      {elseif eq($attribute.contentclass_attribute_identifier, 'usefull_rating')}
        <span class="ezsr-star-rating-label">Molto utile</span>
      {else}
        <span class="ezsr-star-rating-label">Molto</span>
      {/if}


      <span class="hide">Media votazione <span id="ezsr_average_{$attribute.id}" class="average ezsr-average-rating">{$rating.rounded_average|wash}</span> su 4 ( voti <span id="ezsr_total_{$attribute.id}" class="votes">{$rating.rating_count|wash}</span>)</span>
      <p id="ezsr_just_rated_{$attribute.id}" class="ezsr-just-rated hide">Grazie!</p>
      <p id="ezsr_has_rated_{$attribute.id}" class="ezsr-has-rated hide">Puoi esprimere il tuo parere una volta sola</p>
      <p id="ezsr_changed_rating_{$attribute.id}" class="ezsr-changed-rating hide">Grazie per aver aggiornato il tuo parere</p>
    </div>
  {/if}
{/if}

{run-once}
{ezcss_require( 'star_rating.css' )}
{if and( $attribute.data_int|not, has_access_to_limitation( 'ezjscore', 'call', hash( 'FunctionList', 'ezstarrating_rate' ) ))}
  {def $preferred_lib = 'jquery'}
  {ezscript_require( array( 'ezjsc::jquery', 'ezjsc::jqueryio', 'ezstarrating_jquery.js' ) )}
{/if}
{/run-once}
{undef $rating}
