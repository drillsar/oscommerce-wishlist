<script>$(document).ready(function() { $('button[name="wishlist"]').bind('click', function() {
    let input = document.createElement('input');

    input.setAttribute('type', 'hidden');
    input.setAttribute('name', 'wishlist');
    input.setAttribute('value', 'wishlist');

    let form = $(this).closest('form');
    form.unbind('submit');
    form.append(input);
    form.submit();

    return false;
  });});
</script>
