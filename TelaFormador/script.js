
    function abrirRequisicao(){

        document.getElementById("modalRequisicao").style.display="flex";

    }


    /* FECHAR MODAL DA REQUISIÇÃO */

    function fecharRequisicao(){

        document.getElementById("modalRequisicao").style.display="none";

    }



    /* ABRIR MODAL DE AVARIA */

    function abrirAvaria(){

        document.getElementById("modalAvaria").style.display="flex";

    }


    /* FECHAR MODAL DE AVARIA */

    function fecharAvaria(){

        document.getElementById("modalAvaria").style.display="none";

    }



    /* ===============================
       ENVIAR REQUISIÇÃO
    =============================== */

    function enviarRequisicao(){

        let item =
            document.getElementById("item").value;

        let data =
            document.getElementById("dataRequisicao").value;

        let inicio =
            document.getElementById("horaInicio").value;

        let fim =
            document.getElementById("horaFim").value;


        if(data === "" || inicio === "" || fim === ""){

            alert("Preencha todos os campos!");

            return;

        }


        let tabela =
            document.getElementById("tabelaRequisicoes");


        let linha =
            tabela.insertRow();


        linha.innerHTML = `

            <td>${item}</td>

            <td>${data}</td>

            <td>${inicio} - ${fim}</td>

            <td>

                <span class="estado pendente">

                    Pendente

                </span>

            </td>

        `;


        alert("Requisição submetida com sucesso!");

        fecharRequisicao();

    }



    /* ===============================
       REPORTAR AVARIA
    =============================== */

    function enviarAvaria(){

        let codigo =
            document.getElementById("codigoEquipamento").value;

        let descricao =
            document.getElementById("descricaoAvaria").value;


        if(codigo === "" || descricao === ""){

            alert("Preencha todos os campos!");

            return;

        }


        alert(
            "Avaria do equipamento "
            + codigo +
            " reportada com sucesso!"
        );


        document.getElementById("codigoEquipamento").value="";

        document.getElementById("descricaoAvaria").value="";


        fecharAvaria();

    }
