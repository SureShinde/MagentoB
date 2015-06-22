<div class="select-area">
    <span for="category_search" class="placeholder_select_category" id="placeholder_select_category"></span>
    <select id="category" class="category" name="category_search">
        <option value="All Category">{{t._('All')}}</option>
        {% for key, data in category %}
        <option value="{{data['name']}}" {% if _categorySearch == data['name'] %} selected {% endif %}>
            {{data['name']}}
        </option>
        {% endfor %}
    </select>
</div>