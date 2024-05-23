<form id="simple_contact_form" style="display:flex; flex-direction:column; gap: 2px; width:25%">
    <?php wp_nonce_field('wp_rest'); ?> <!-- portect form from bots -->
    <label>Name:</label>
    <input type="text" name="name">
    <label>Email:</label>
    <input type="text" name="email">
    <label>Phone:</label>
    <input type="text" name="phone">
    <label>Message:</label>
    <textarea name="message"></textarea>
    <button type="submit">Submit</button>
</form>

<script>
    document.getElementById("simple_contact_form").addEventListener("submit", process_form);

    function process_form(event) {
        event.preventDefault();

        const formData = new FormData(event.target);
        fetch("<?php echo get_rest_url(null, "v1/simple-contact-form/process_form"); ?>", {
            method: "POST",
            body: formData
        }).then(response => {
            if (!response.ok) throw new Error(`HTTP Error! Status: ${response.status}`);
        }).catch(error => console.log(`Simple Contact Form Error: ${error.message}`));
    }
</script>