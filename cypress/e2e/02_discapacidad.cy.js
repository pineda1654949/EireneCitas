/**
 * DIS-01..03: datos de discapacidad en la ficha del paciente y su
 * visualizacion (insignia) en el listado administrativo.
 */
describe('Discapacidad en pacientes', () => {
    beforeEach(() => {
        cy.login('recepcion@eirene.test');
    });

    it('DIS-01 registra un paciente con discapacidad y aparece la insignia en el listado', () => {
        const dni = String(Date.now()).slice(-8);

        cy.visit('/admin/pacientes/create');
        cy.get('#nombres').type('Luz');
        cy.get('#apellidos').type('Vargas');
        cy.get('#dni').type(dni);
        cy.get('input[name="tiene_discapacidad"][type="checkbox"]').check();
        cy.get('#tipo_discapacidad').select('visual');
        cy.get('#necesidades_especiales').type('Usa baston guia.');
        cy.contains('button', /guardar paciente/i).click();

        cy.url().should('include', '/admin/pacientes');
        cy.contains('Paciente registrado correctamente.').should('be.visible');
        cy.contains('tr', dni).contains('Discapacidad').should('be.visible');
    });

    it('DIS-02 el tipo de discapacidad es obligatorio si se marca la casilla', () => {
        cy.visit('/admin/pacientes/create');
        cy.get('#nombres').type('Ana');
        cy.get('#apellidos').type('Rios');
        cy.get('input[name="tiene_discapacidad"][type="checkbox"]').check();
        cy.contains('button', /guardar paciente/i).click();

        cy.url().should('include', '/admin/pacientes/create');
        cy.get('#tipo_discapacidad-error').should('be.visible');
    });

    it('DIS-03 edita un paciente sin discapacidad para marcarla y la insignia aparece', () => {
        const dni = String(Date.now()).slice(-8);

        cy.visit('/admin/pacientes/create');
        cy.get('#nombres').type('Pedro');
        cy.get('#apellidos').type('Salas');
        cy.get('#dni').type(dni);
        cy.contains('button', /guardar paciente/i).click();

        cy.contains('tr', dni).contains('Discapacidad').should('not.exist');
        cy.contains('tr', dni).contains('a', /ficha/i).click();

        cy.get('input[name="tiene_discapacidad"][type="checkbox"]').check();
        cy.get('#tipo_discapacidad').select('auditiva');
        cy.contains('button', /guardar cambios/i).click();

        cy.url().should('include', '/admin/pacientes');
        cy.contains('tr', dni).contains('Discapacidad').should('be.visible');
    });
});
