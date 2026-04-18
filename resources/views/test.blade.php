<div class="column min_height around">
    <div class="column gap_20 vertical_center full_width text_center">
        <h1>Test page</h1>
        <h5>Send messages to the machine with MQTT</h5>
    </div>

    <div class="column vertical_center full_width">
        <form class="column gap_20 padding_vertical_15 end box_focus_mode padding_orizontal_10 box" id="messagemqtt">
            @csrf
            <textarea id="message" placeholder="Scrivi qualcosa..." required></textarea>
            <button type="submit">Invia</button>
        </form>
    </div>
</div>

@vite(['resources/js/pages/test.js'])
