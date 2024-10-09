# Reescribe atributo ALT
Reescribe el atributo ALT de las imágenes para mejorar la accesibilidad.
## Activar y configurar el plugin
- Guarda el archivo reescribe-atributo-alt.php en la carpeta wp-content/plugins/reescribe-atributo-alt/.
- Accede al panel de administración de WordPress y ve a la sección Plugins.
- Activa el plugin "Reescribe atributo ALT".
- Configura las opciones del plugin navegando a Ajustes > Reescribe atributo alt.
## Probar el plugin
- Crear o editar una publicación o página y añadir imágenes sin atributo ALT o con atributo ALT existente.
- Visualizar la publicación para verificar que los atributos ALT de las imágenes se han modificado según la configuración del plugin.
## Notas adicionales
- Compatibilidad UTF-8: Se utiliza mb_convert_encoding para asegurar que los caracteres especiales se manejan correctamente.
- Seguridad: Se verifica si se accede directamente al archivo y se evita para mejorar la seguridad.
- Extracción del nombre del archivo: Se utiliza parse_url y pathinfo para obtener correctamente el nombre del archivo de la imagen sin extensión, incluso si la URL tiene parámetros adicionales.
## Consideraciones Importantes
- Validación de entradas: Aunque las opciones son configuradas por el administrador, es recomendable validar y sanitizar los datos si se amplía el plugin.
- Soporte Multisitio: Si usas WordPress Multisitio, asegúrate de que el plugin funciona correctamente en ese entorno.
- Internacionalización: El plugin está preparado para traducción utilizando el dominio de texto alt-attribute-rewriter.
## Posibles Mejoras
- Soporte para imágenes con srcset: Si tus imágenes utilizan el atributo srcset, podrías ampliar el plugin para manejar esos casos.
- Caché: Si notas problemas de rendimiento, considera implementar algún mecanismo de caché o optimizar la manipulación del DOM.
- Opciones adicionales: Podrías añadir más opciones para personalizar el separador entre los elementos del atributo ALT o incluir otros metadatos.
