<script>
     function(){
         $('.{{load}}-{{content['identifier']}}').bxSlider({
		      controls: false
         });
     };
 </script>
 <div   {% if content['size']['width'] is defined %}width  = "{{content['size']['width']}}px" {% endif %}
        {% if content['size']['width'] is defined %}height = "{{content['size']['height']}}px" {% endif %}
        class="wrapper-static-area-slider slider-html">
     
     <ul class="static-area {{load}}-{{content['identifier']}}">
         {% for key, data in content['contents'] %}
             <li>{{data['text']}}</li>
         {% endfor %}
     </ul>
     
 </div>
