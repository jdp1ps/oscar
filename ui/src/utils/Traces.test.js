import { describe, it, expect} from "vitest";
import Traces from "./Traces.js";

describe("Traces", () => {
    it('Affiche bien les personnes', ()=> {
        expect(Traces.log('foo')).equal('foo');
        expect(
            Traces.log('[Person:4744:Claire Gressin-Hebert] a supprimé le jalon 31 Dec 2019 (Rapport final) dans l\'activité')
        ).equal('<a href="/person/show/4744" class="person">Claire Gressin-Hebert</a> a supprimé le jalon 31 Dec 2019 (Rapport final) dans l\'activité');

        expect(
            Traces.log('a ajouté [Organization:11459:CEMU (IFS)] (Composante responsable) dans')
        ).equal(`a ajouté <a href="/organization/show/11459" class="organization">CEMU (IFS)</a> (Composante responsable) dans`);
    });
});