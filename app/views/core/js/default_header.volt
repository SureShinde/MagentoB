{{ assets.outputJs('header') }}

<script>
    var baseUri = "{{ baseUri }}";
    var customerIsLogin = '{{ isCustomerLogin }}';
    
    {#
    {% if validationTranslation %}
        {% for key, value in validationTranslation %}
            var {{ 'validate_' ~ key }} = "{{ value }}";
        {% endfor %}
    {% endif %}
    #}
</script>
