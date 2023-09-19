function verificaNomeUsuario() {
    const usuarioInput = document.getElementById("usuario");
    const usuarioValidationMessage = document.getElementById("usuarioValidationMessage");

    usuarioInput.addEventListener("input", function() {
        const usuario = usuarioInput.value;

        // Enviar uma solicitação AJAX para verificar_usuario.php
        fetch("verificar_usuario.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `usuario=${encodeURIComponent(usuario)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.valid) {
                usuarioValidationMessage.textContent = "Nome de usuário disponível.";
                usuarioValidationMessage.style.color = "green";
            } else {
                usuarioValidationMessage.textContent = "Nome de usuário já está em uso.";
                usuarioValidationMessage.style.color = "red";
            }
        })
        .catch(error => {
            console.error("Erro na solicitação AJAX: " + error);
        });
    });
}



