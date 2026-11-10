/**
 * REC-01..04: gestion de cuentas de recepcionista (solo administrador).
 */
describe('Recepcionistas', () => {
    beforeEach(() => {
        cy.login('admin');
    });

    it('REC-01 registra una nueva recepcionista', () => {
        const correo = `recepcion${Date.now()}@eirene.test`;

        cy.visit('/admin/recepcionistas/create');
        cy.get('#name').type('Carmen');
        cy.get('#apellidos').type('Diaz');
        cy.get('#email').type(correo);
        cy.get('#password').type('Recepcion2026');
        cy.contains('button', /registrar recepcionista/i).click();

        cy.url().should('include', '/admin/recepcionistas');
        cy.contains('Recepcionista registrada correctamente.').should('be.visible');
        cy.contains(correo).should('be.visible');
    });

    it('REC-02 el formulario vacio muestra los errores de campos obligatorios', () => {
        cy.visit('/admin/recepcionistas/create');
        cy.contains('button', /registrar recepcionista/i).click();

        cy.url().should('include', '/admin/recepcionistas/create');
        cy.get('#name-error').should('be.visible');
        cy.get('#apellidos-error').should('be.visible');
        cy.get('#email-error').should('be.visible');
    });

    it('REC-03 edita una recepcionista y la desactiva', () => {
        const correo = `editar${Date.now()}@eirene.test`;

        cy.visit('/admin/recepcionistas/create');
        cy.get('#name').type('Noelia');
        cy.get('#apellidos').type('Torres');
        cy.get('#email').type(correo);
        cy.get('#password').type('Recepcion2026');
        cy.contains('button', /registrar recepcionista/i).click();

        cy.contains('tr', correo).contains('a', /editar/i).click();
        cy.get('#name').clear().type('Noelia Fernanda');
        cy.get('input[name="activo"][type="checkbox"]').uncheck();
        cy.contains('button', /guardar cambios/i).click();

        cy.url().should('include', '/admin/recepcionistas');
        cy.contains('Datos actualizados correctamente.').should('be.visible');
        cy.contains('tr', correo).contains('Inactiva').should('be.visible');
    });

    it('REC-04 elimina una recepcionista sin pagos asociados', () => {
        const correo = `borrar${Date.now()}@eirene.test`;

        cy.visit('/admin/recepcionistas/create');
        cy.get('#name').type('Temporal');
        cy.get('#apellidos').type('Prueba');
        cy.get('#email').type(correo);
        cy.get('#password').type('Recepcion2026');
        cy.contains('button', /registrar recepcionista/i).click();
        cy.contains(correo).should('be.visible');

        cy.on('window:confirm', () => true);
        cy.contains('tr', correo).contains('button', /eliminar/i).click();

        cy.contains('Recepcionista eliminada.').should('be.visible');
        cy.contains(correo).should('not.exist');
    });
});
