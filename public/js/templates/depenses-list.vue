<section class="depenses-list">
    <h2>
        <i class="icon-calculator"></i>
        Depenses
    </h2>
    <div class="lc" :class="{ pending: loading }">
        <div class="lc-mask">
            <div class="lc-alert">
                Error message
            </div>
            <div class="lc-loader">
                <span class="text">Chargement</span>
            </div>
        </div>
        <section class="lc-content">
            <article v-for="depense in depenses" class="card xs" :class="'depense-' +depense.type">
                <h3 class="card-title">
                    <strong>{{ depense.label }}</strong>
                    <span class="montant">{{ depense.amount | money}}</span>
                </h3>
                <div class="card-content text-highlight">
                    Le <time :datetime="depense.date">{{ depense.date | dateFull }}</time> <br>
                    N° de pièce <strong>{{ depense.numero }}</strong><br>
                    Type : <strong>{{ depense.type }}</strong>
                    <p>{{ depense.description }}</p>
                </div>
                <nav class="card-footer">
                    <a href="#">Détails</a>
                </nav>
            </article>
        </section>
        <section class="total row">
            <div class="col-md-4">Total : </div>
            <div class="col-md-8">{{ total }}</div>
        </section>
    </div>
    <button @click.prevent="fetch">Recharger les dépenses</button>
    <button @click.prevent="loading = !loading">test pending</button>
</section>