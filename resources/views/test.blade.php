<div class="column min_height around">
    <div class="column gap_20 vertical_center full_width text_center">
        <h1>Test page</h1>
        <h5>Send messages to the machine with MQTT</h5>
    </div>

    <div class="column vertical_center orizontal_center gap_20 full_width">
        <div class="row gap_20 between">
            <h6 class="text_center">Stato connessione: <p id="status">Disconnected</p></h6>
            <div class="row box padding_orizontal_10 vertical_center mobile_row gap_10">
                <h6><label for="topic">Topic:</label></h6>
                <input type="text" id="topic" placeholder="Topic">
            </div>
        </div>
        <form class="column gap_20 padding_vertical_15 end box_focus_mode padding_orizontal_10 box" id="messagemqtt">
            @csrf
            <textarea id="message" placeholder="Scrivi qualcosa..." required></textarea>
            <button type="submit">Invia</button>
        </form>
    </div>
</div>

@vite(['resources/js/pages/test.js'])
