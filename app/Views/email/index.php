<div class="container-fluid">
    <div class="card shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-12">
                    <h4 class="fw-semibold mb-8">Configuración de Email</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="<?= base_url() ?>">Inicio</a></li>
                            <li class="breadcrumb-item" aria-current="page">Gestión de Email</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="<?= base_url('email/store') ?>" method="post">
                        <?= csrf_field() ?>
                        
                        <h5 class="mb-3">Información del Remitente</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="fromEmail" name="fromEmail" placeholder="noreply@tuempresa.com" value="<?= esc($settings['fromEmail'] ?? '') ?>" required>
                                    <label for="fromEmail">Email del Remitente</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="fromName" name="fromName" placeholder="Proxmox Alert" value="<?= esc($settings['fromName'] ?? 'Proxmox Alert') ?>" required>
                                    <label for="fromName">Nombre del Remitente</label>
                                </div>
                            </div>
                        </div>

                        <h5 class="mb-3 border-top pt-4">Servidor SMTP</h5>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="SMTPHost" name="SMTPHost" placeholder="smtp.gmail.com" value="<?= esc($settings['SMTPHost'] ?? '') ?>" required>
                                    <label for="SMTPHost">Servidor SMTP</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="SMTPPort" name="SMTPPort" placeholder="587" value="<?= esc($settings['SMTPPort'] ?? '587') ?>" required>
                                    <label for="SMTPPort">Puerto</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="SMTPUser" name="SMTPUser" placeholder="usuario" value="<?= esc($settings['SMTPUser'] ?? '') ?>" required>
                                    <label for="SMTPUser">Usuario SMTP</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3 position-relative">
                                    <input type="password" class="form-control" id="SMTPPass" name="SMTPPass" placeholder="contraseña" value="<?= esc($settings['SMTPPass'] ?? '') ?>" required>
                                    <label for="SMTPPass">Contraseña SMTP</label>
                                    <button class="btn position-absolute top-50 end-0 translate-middle-y me-2 border-0" type="button" onclick="togglePassword()">
                                        <i class="ti ti-eye fs-5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="SMTPCrypto" name="SMTPCrypto">
                                        <option value="tls" <?= ($settings['SMTPCrypto'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS (Recomendado)</option>
                                        <option value="ssl" <?= ($settings['SMTPCrypto'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                        <option value="" <?= ($settings['SMTPCrypto'] ?? '') === '' ? 'selected' : '' ?>>Ninguno</option>
                                    </select>
                                    <label for="SMTPCrypto">Cifrado de Seguridad</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="protocol" name="protocol">
                                        <option value="smtp" <?= ($settings['protocol'] ?? 'smtp') === 'smtp' ? 'selected' : '' ?>>SMTP</option>
                                        <option value="mail" <?= ($settings['protocol'] ?? '') === 'mail' ? 'selected' : '' ?>>PHP Mail</option>
                                    </select>
                                    <label for="protocol">Protocolo de Envío</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="mailType" name="mailType">
                                        <option value="html" <?= ($settings['mailType'] ?? 'html') === 'html' ? 'selected' : '' ?>>HTML</option>
                                        <option value="text" <?= ($settings['mailType'] ?? '') === 'text' ? 'selected' : '' ?>>Texto Plano</option>
                                    </select>
                                    <label for="mailType">Tipo de Contenido</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4 border-top pt-4">
                            <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center justify-content-end gap-2">
                                    <button type="submit" formaction="<?= base_url('email/test') ?>" class="btn btn-outline-info font-medium px-4">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <i class="ti ti-send me-2 fs-4"></i>
                                            Probar Configuración
                                        </div>
                                    </button>
                                    <button type="submit" class="btn btn-primary font-medium px-4">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <i class="ti ti-device-floppy me-2 fs-4"></i>
                                            Guardar Cambios
                                        </div>
                                    </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('SMTPPass');
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
