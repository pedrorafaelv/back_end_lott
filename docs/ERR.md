Código HTTP	Código Interno (Ejemplo)	Significado (Español)	Descripción / Cuándo ocurre
400	ERR-001	Solicitud incorrecta	La petición no tiene el formato correcto (JSON inválido, campos faltantes o mal escritos).
400	ERR-002	Número de figura inválido	El número de figura proporcionado no es válido (fuera de rango, no existe en el sorteo actual).
400	ERR-003	Monto de apuesta inválido	Monto apostado no es permitido (no es múltiplo de la unidad permitida, o fuera de rango min-max).
401	ERR-004	No autenticado	 El token de acceso no fue proporcionado o es inválido.
403	ERR-005	Sin permisos	El usuario no tiene permisos para realizar esa acción 
404	ERR-006	Sorteo no encontrado	El ID del sorteo consultado no existe o ya fue eliminado.
404	ERR-007	Figura no encontrada	La figura específica solicitada no existe en la base de datos.
409	ERR-008	Apuesta duplicada	El usuario ya realizó una apuesta idéntica para esa figura y sorteo (evita duplicados).
409	ERR-009	Sorteo ya cerrado	No se pueden realizar apuestas porque el sorteo ya finalizó o está en proceso de resultados.
422	ERR-010	Saldo insuficiente	El usuario no tiene fondos suficientes para realizar la apuesta.
422	ERR-011	Límite de apuestas excedido	El usuario ha superado el límite máximo de apuestas permitidas por sorteo o por figura.
429	ERR-012	Demasiadas peticiones	 El usuario ha superado la tasa de solicitudes permitida (rate limiting).
500	ERR-013	Error interno del servidor	Error inesperado en el servidor (base de datos caída, excepción no capturada).
503	ERR-014	Servicio no disponible	El servicio de lotería está en mantenimiento o temporalmente fuera de línea.
504	ERR-015	Tiempo de espera agotado	La API no pudo responder a tiempo (timeout) al consultar un servicio o procesar una operación.
409 ERR-016 Sorteo Iniciado      No se puede eliminar la apuesta el sorteo ya ha iniciado
404 ERR-017 Apuesta no existe    La apuesta no existe o no pertenece al usuario
404 ERR-018 Cuenta no encontrada  El Usuario no tiene cuenta 
500 ERR-019 Cancelación Fallida   Error en la cancelacion de la jugada
500 ERR-020 Fallo inesperado   Ha ocurrido un error mientras se cancelaba la jugada
404 ERR-021 no hay cartones     No se encontraron cartones 
404 ERR-022 grupo de fichas no encontrado  no se ha encontrado un grupo de fichas 
404 ERR-023 No hay fichas                          no se ha encontrado fichas para este grupo de fichas  
409 ERR-024 Sorteo terminado por linea     Raffle already has a line winner and has ended
409 ERR-025 Sorteo terminado full   Raffle already has a full winner and has ended
409 ERR-026 NoPrizes enabled        Raffle has no prizes enabled (reward_line and reward_full are disabled)
422 ERR-027 Not Assigned card       The card could not be added to the raffle.
409 ERR-028 Raffle started          No se puede realizar la apuesta porque el sorteo ya ha iniciado tiene mas de dos fichas
409 ERR-029 Not account             The user haven't an account
409 ERR-030 Account not be updated  The card was added to the raffle, but account could not be updated.
422 ERR-031 Card not available      The card is not available for the raffle
404 ERR-032 User not Found          The User was not found
404 ERR-033
401 ERR-034 haven´t permissionsof raffle   The User haven´t Permissions over the Raffle  
ERR-035
ERR-036
404 ERR-037 groupFicha Not found      The GroupFicha not found
403 ERR-038 not groupFichas register  Not groupFichas register
401 ERR-039 raffle was not created    The Raffle could not be created
401 ERR-040 Error to create Raffle    Error to create Raffle, Raffle(s) in progress'
404 ERR-041 currencies Not found      Currencies Not found
