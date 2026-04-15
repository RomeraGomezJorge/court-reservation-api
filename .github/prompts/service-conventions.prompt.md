Conveciones de los servicios

- Ubicación: app/Services/{NombreDelServicio}Service.php
- Nombre: Usar sufijo Service (ej: UserCreateService.php, UserRegisterService.php)
- Principio de responsabilidad única: Cada servicio debe hacer una sola cosa bien
- Método público único: Generalmente llamado handle() o execute()
- Tipado estricto: Todos los parámetros y retornos con tipo explícito
- Transacciones: Usar DB::transaction() para operaciones que requieran consistencia es decir cuando se esten guardando mas de un datos en la base de datos
- Mantén la lógica de negocios compleja en los servicios, no en los controladores.
- SIEMPRE que crees un servicio, sigue el principio YAGNI: no agregar funcionalidad que no se usa.
- SIEMPRE que crees un servicio, sigue el principio KISS: mantener el código simple y directo.
- Si el servicio se usa solo en un método del controlador, debe inyectarse directamente en ese método mediante type-hinting.
- Si el servicio se usa en múltiples métodos, debe inicializarse en el constructor del controlador.
- Cuando declares una variable local o propiedad del controlador que contenga una instancia de un servicio, no utilices el prefijo service.
    - ✔️ Ejemplo correcto: $userCreator
    - ❌ Ejemplo incorrecto: $userCreatorService
      Cuando generes código, favorece soluciones simples y explícitas que faciliten comprender qué sucede.
- Reduce la profundidad innecesaria de capas: una operación no debe atravesar media docena de clases para ejecutarse.
- Acepta pequeñas duplicaciones si evitan abstracciones prematuras.
- La prioridad es que el sistema sea fácil de depurar, extender y arreglar rápidamente en producción.
- Un método de 40 líneas claro vale más que cinco capas “limpias” pero difíciles de rastrear.
- El nombre del Servicio debe expresar claramente su intención y su razón de existir.
- El método handle() debe describir QUÉ hace el servicio, no CÓMO lo hace.
- ~~~~Para lograrlo, la lógica debe descomponerse en métodos privados con nombres que revelen intención.
- El flujo del handle() debe poder leerse como una secuencia de acciones en lenguaje casi natural.
